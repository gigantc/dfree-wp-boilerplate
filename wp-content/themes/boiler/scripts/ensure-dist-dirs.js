#!/usr/bin/env node

/**
 * Ensure /dist directory structure exists
 * Runs before build and dev commands
 */

const fs = require('fs');
const path = require('path');

const dirs = [
  '../dist',
  '../dist/css',
  '../dist/js',
  '../dist/js/libs',
  '../dist/js/blocks',
  '../dist/js/components',
  '../dist/js/singles'
];

dirs.forEach(dir => {
  const fullPath = path.join(__dirname, dir);
  if (!fs.existsSync(fullPath)) {
    fs.mkdirSync(fullPath, { recursive: true });
    console.log(`✅ Created directory: ${dir}`);
  }
});

console.log('✅ All dist directories ready');
