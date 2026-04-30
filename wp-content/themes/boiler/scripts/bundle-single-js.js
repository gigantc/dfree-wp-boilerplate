#!/usr/bin/env node

/**
 * Bundle all single page section JavaScript files
 * Each section's JS file gets bundled separately to dist/js/singles/
 */

const path = require('path');
const { bundleJs } = require('./lib/bundle-js');

const isProd = process.argv.includes('--prod');

bundleJs({
  sourceDir: path.join(__dirname, '../singles'),
  outputDir: path.join(__dirname, '../dist/js/singles'),
  type: 'single',
  isProd
}).then(({ failures }) => {
  if (failures > 0) process.exit(1);
});
