import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
const { createFactorySupervision } = await import(pathToFileURL(path.resolve(process.env.VIA_FACTORY_ADAPTER_PATH || '.ci-via/runtime/factory-supervision.js')));
const ids = JSON.parse(fs.readFileSync('ci-results/supervision-ids.json', 'utf8'));
// Public disposable fixture strings; never provision these in a shared environment.
const env = {
  NODE_ENV: 'test', VIA_FACTORY_SUPERVISION_ENABLED: 'true',
  VIA_FACTORY_OWNER_TOKEN: 'ci-only-owner-credential-0000000000000000',
  FACTORY_VIA_READ_TOKEN: 'ci-only-reader-credential-000000000000000',
  FACTORY_VIA_CORRECTION_TOKEN: 'ci-only-correct-credential-00000000000000',
  VIA_FACTORY_BASE_URL: 'http://factory:8080',
};
let httpCalls = 0;
const handle = createFactorySupervision({ env, transport: (url, options) => {
  // Only translate Docker service DNS to its loopback-published port.
  assert.equal(url.origin, 'http://factory:8080');
  const local = new URL(url);
  local.hostname = '127.0.0.1';
  httpCalls++;
  return fetch(local, options); // Real HTTP response from Laravel, no response mocks.
} });
const authorization = 'Bearer ' + env.VIA_FACTORY_OWNER_TOKEN;
const read = { method: 'GET', pathname: '/api/factory-supervision/missions/' + ids.mission, authorization };
if (process.argv[2] === 'correct') {
  assert.equal((await handle({ ...read, authorization: '' })).status, 401);
  assert.equal(httpCalls, 0);
  const before = await handle(read);
  assert.equal(before.status, 200);
  assert.equal(before.payload.mission.status, 'blocked');
  assert.equal(before.payload.mission.artifact_gate_passed, false);
  assert.equal(before.payload.steps.find(s => s.id === ids.first).reason, 'approved_artifact_changed');
  assert.equal(before.payload.steps.find(s => s.id === ids.second).status, 'pending');
  const correction = {
    method: 'POST', pathname: read.pathname + '/steps/' + ids.first + '/correction', authorization,
    body: { reason: 'Correct CI artifact corruption', expected_attempt: 1 },
  };
  assert.equal((await handle({ ...correction, body: { ...correction.body, approved: true } })).status, 422);
  const accepted = await handle(correction);
  assert.equal(accepted.status, 202);
  assert.equal(accepted.payload.approval_granted, false);
  assert.equal(accepted.payload.execution_started, false);
  assert.equal((await handle(correction)).status, 409);
  const pending = await handle(read);
  assert.equal(pending.payload.mission.status, 'blocked');
  assert.equal(pending.payload.steps.find(s => s.id === ids.second).status, 'pending');
} else if (process.argv[2] === 'verify') {
  const after = await handle(read);
  assert.equal(after.status, 200);
  assert.equal(after.payload.mission.status, 'completed');
  assert.equal(after.payload.mission.artifact_gate_passed, true);
  assert.equal(after.payload.mission.independent_qa_verified, false);
  assert.equal(after.payload.steps.find(s => s.id === ids.first).attempt, 2);
} else throw new Error('Expected correct or verify');
console.log('VIA -> Factory real HTTP ' + process.argv[2] + ': PASS (' + httpCalls + ' requests)');
