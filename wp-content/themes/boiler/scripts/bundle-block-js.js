#!/usr/bin/env node

/**
 * Bundle all block JavaScript files
 * Each block's JS file gets bundled separately to dist/js/blocks/
 */

const path = require('path');
const { bundleJs } = require('./lib/bundle-js');

const isProd = process.argv.includes('--prod');

bundleJs({
  sourceDir: path.join(__dirname, '../blocks'),
  outputDir: path.join(__dirname, '../dist/js/blocks'),
  type: 'block',
  isProd
}).then(({ failures }) => {
  if (failures > 0) process.exit(1);
});
