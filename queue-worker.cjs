#!/usr/bin/env node
const { spawn } = require('child_process');
const path = require('path');

const projectRoot = 'd:\\admin-crud-git\\CRM-Minimal-Carbon';

console.log('Starting Laravel Queue Worker...');
console.log(`Project root: ${projectRoot}`);

const worker = spawn('php', ['artisan', 'queue:work', '--queue=default', '--tries=3', '--sleep=3'], {
  cwd: projectRoot,
  stdio: 'inherit',
  shell: true
});

worker.on('exit', (code, signal) => {
  console.log(`Queue worker exited with code ${code} and signal ${signal}`);
  process.exit(code);
});

process.on('SIGTERM', () => {
  console.log('SIGTERM signal received: closing worker');
  worker.kill('SIGTERM');
});

process.on('SIGINT', () => {
  console.log('SIGINT signal received: closing worker');
  worker.kill('SIGINT');
});
