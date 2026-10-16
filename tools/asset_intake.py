"""Transport-neutral MCP tool; authentication/resolution are host-owned ports.

Never accept an identity, checkout, token, URL or local source from tool args.
The host must enforce quotas and exclusive ownership of each granted checkout.
"""
from dataclasses import dataclass
import hashlib
import os
from pathlib import Path
import re
import tempfile
from asset_ingest import MAX_BYTES, clean_checkout, git, safe_parts
from asset_job import run_materialized


@dataclass(frozen=True)
class Grant:
    principal: str
    project: str
    branch: str
    checkout: Path
    prefixes: tuple[str, ...]
    max_bytes: int = MAX_BYTES


class AssetIntake:
    def __init__(self, *, authorize, open_reference, reserve, audit, intake_root):
        """Trusted host callbacks, never supplied by the model/tool caller.

        authorize(context, project, branch) -> Grant (or raises).
        open_reference(context, reference) -> context manager of binary stream;
          MUST check file ownership/ACL/expiry and stream original bytes only.
        reserve(grant) -> context manager enforcing quotas AND exclusive checkout
          access across workers. Hold until audit has completed.
        audit(event) -> durable audit sink; failure before staging is fail-closed.
        intake_root -> dedicated server-owned directory, outside all checkouts.
        """
        self.authorize = authorize
        self.open_reference = open_reference
        self.reserve = reserve
        self.audit = audit
        self.intake_root = Path(intake_root).resolve(strict=True)
        info = self.intake_root.stat()
        if info.st_uid != os.geteuid() or info.st_mode & 0o077:
            raise PermissionError('intake root must be owner-only')

    def receive(self, context, *, file_reference, project, branch, destination, sha256):
        fields = (file_reference, project, branch, destination, sha256)
        if not all(isinstance(x, str) and x for x in fields):
            raise ValueError('invalid request')
        if not re.fullmatch(r'[A-Za-z0-9_-]{1,160}', file_reference):
            raise ValueError('opaque file reference required')
        if not re.fullmatch(r'[0-9a-fA-F]{64}', sha256):
            raise ValueError('invalid SHA-256')
        parts = safe_parts(destination)
        grant = self.authorize(context, project, branch)
        if not isinstance(grant, Grant) or not grant.principal or (grant.project, grant.branch) != (project, branch):
            raise PermissionError('invalid authorization grant')
        if branch in ('main', 'master', 'production') or not grant.prefixes:
            raise PermissionError('working branch and destination grant required')
        if not any(parts[:len(safe_parts(p))] == safe_parts(p) and len(parts) > len(safe_parts(p)) for p in grant.prefixes):
            raise PermissionError('destination outside grant')
        if not 0 < grant.max_bytes <= MAX_BYTES:
            raise PermissionError('invalid quota')
        root = Path(grant.checkout).resolve(strict=True)
        if self.intake_root == root or self.intake_root.is_relative_to(root):
            raise PermissionError('intake must be outside checkout')
        event = {'principal': grant.principal, 'project': project, 'branch': branch,
                 'path': destination, 'sha256': sha256.lower()}
        with self.reserve(grant):
            clean_checkout(root, branch)
            head = git(root, 'rev-parse', 'HEAD')
            self.audit(dict(event, status='authorized', base_commit=head))
            with tempfile.TemporaryDirectory(prefix='asset-', dir=self.intake_root) as jobdir:
                source = Path(jobdir) / 'original'
                digest = hashlib.sha256()
                total = 0
                with self.open_reference(context, file_reference) as stream, source.open('xb') as out:
                    os.chmod(source, 0o600)
                    while chunk := stream.read(min(1024 * 1024, grant.max_bytes - total + 1)):
                        if not isinstance(chunk, bytes):
                            raise ValueError('resolver must stream binary bytes')
                        total += len(chunk)
                        if total > grant.max_bytes:
                            raise ValueError('quota exceeded')
                        digest.update(chunk)
                        out.write(chunk)
                    out.flush()
                    os.fsync(out.fileno())
                if digest.hexdigest() != sha256.lower():
                    raise ValueError('SHA-256 mismatch')
                clean_checkout(root, branch)
                if git(root, 'rev-parse', 'HEAD') != head:
                    raise ValueError('checkout changed during transfer')
                result = run_materialized({'source': str(source), 'checkout': str(root),
                                           'destination': destination, 'sha256': sha256})
                receipt = dict(event, status='staged', base_commit=head, bytes=total,
                               requires_git_review=True, requires_hml_validation=True)
                # If the sink fails, propagate failure; never claim an audited success.
                self.audit(receipt)
                return dict(result, receipt=receipt)


def register_mcp(server, intake, get_context):
    """Register on an existing authenticated MCP host; no new public server.

    get_context MUST return verified request-local identity from middleware.
    Never derive it from tool arguments or an unverified request header.
    """
    @server.tool(name='factory_asset_receive')
    def factory_asset_receive(file_reference: str, project: str, branch: str,
                              destination: str, sha256: str) -> dict:
        return intake.receive(get_context(), file_reference=file_reference,
                              project=project, branch=branch,
                              destination=destination, sha256=sha256)
    return factory_asset_receive
