#!/usr/bin/env node

/**
 * Bundle all component JavaScript files
 * Each component's JS file gets bundled separately to dist/js/components/
 */

const path = require('path');
const { bundleJs } = require('./lib/bundle-js');

const isProd = process.argv.includes('--prod');

bundleJs({
  sourceDir: path.join(__dirname, '../components'),
  outputDir: path.join(__dirname, '../dist/js/components'),
  type: 'component',
  isProd
}).then(({ failures }) => {
  if (failures > 0) process.exit(1);
});
