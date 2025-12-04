#!/usr/bin/env node

/**
 * Bundle all block JavaScript files
 * Each block's JS file gets bundled separately to js/blocks/
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const blocksDir = path.join(__dirname, '../blocks');
const outputDir = path.join(__dirname, '../js/blocks');

// Create output directory if it doesn't exist
if (!fs.existsSync(outputDir)) {
  fs.mkdirSync(outputDir, { recursive: true });
}

/**
 * Recursively find all .js files in blocks directory
 */
function findBlockJsFiles(dir) {
  let jsFiles = [];

  const entries = fs.readdirSync(dir, { withFileTypes: true });

  for (const entry of entries) {
    const entryPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      jsFiles.push(...findBlockJsFiles(entryPath));
    } else if (
      entry.isFile() &&
      entry.name.endsWith('.js') &&
      !entry.name.startsWith('_') // Skip files starting with _
    ) {
      jsFiles.push(entryPath);
    }
  }

  return jsFiles;
}

// Find all block JS files
const jsFiles = findBlockJsFiles(blocksDir);

if (jsFiles.length === 0) {
  console.log('⚠️  No block JavaScript files found');
  process.exit(0);
}

console.log(`📦 Bundling ${jsFiles.length} block JavaScript file(s)...`);

let successCount = 0;
let errorCount = 0;

// Bundle each JS file separately
for (const jsFile of jsFiles) {
  const fileName = path.basename(jsFile, '.js');
  const outputFile = path.join(outputDir, `${fileName}.min.js`);

  try {
    // Use esbuild to bundle the file
    execSync(
      `npx esbuild "${jsFile}" --bundle --minify --outfile="${outputFile}" --sourcemap --format=iife`,
      { stdio: 'inherit' }
    );
    successCount++;
  } catch (error) {
    console.error(`❌ Failed to bundle ${fileName}.js`);
    errorCount++;
  }
}

console.log(`✅ Bundled ${successCount} block JS file(s) to js/blocks/`);
if (errorCount > 0) {
  console.log(`❌ Failed to bundle ${errorCount} file(s)`);
  process.exit(1);
}
