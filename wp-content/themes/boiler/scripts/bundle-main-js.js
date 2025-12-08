#!/usr/bin/env node

/**
 * Bundle all JavaScript files in src/js into main.min.js
 * Automatically includes any .js files you add to src/js/
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const srcDir = path.join(__dirname, '../src/js');
const outputFile = path.join(__dirname, '../js/main.min.js');

/**
 * Find all .js files in src/js (excluding libs subdirectory)
 */
function findJsFiles(dir) {
  let jsFiles = [];
  const entries = fs.readdirSync(dir, { withFileTypes: true });

  for (const entry of entries) {
    const entryPath = path.join(dir, entry.name);

    // Skip libs directory
    if (entry.isDirectory() && entry.name === 'libs') {
      continue;
    }

    if (entry.isDirectory()) {
      jsFiles.push(...findJsFiles(entryPath));
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

// Find all JS files
const jsFiles = findJsFiles(srcDir);

if (jsFiles.length === 0) {
  console.log('⚠️  No JavaScript files found in src/js/');
  process.exit(0);
}

console.log(`📦 Bundling ${jsFiles.length} JavaScript file(s) into main.min.js...`);
jsFiles.forEach(file => {
  console.log(`   ↳ ${path.relative(srcDir, file)}`);
});

// Create a temporary entry file that imports all JS files
const tempEntry = path.join(__dirname, '../.temp-main-entry.js');
const imports = jsFiles.map(file => {
  const relativePath = path.relative(path.dirname(tempEntry), file).replace(/\\/g, '/');
  return `import './${relativePath}';`;
}).join('\n');

fs.writeFileSync(tempEntry, imports);

try {
  // Bundle the temporary entry file
  execSync(
    `npx esbuild "${tempEntry}" --bundle --minify --outfile="${outputFile}" --sourcemap --format=iife`,
    { stdio: 'inherit' }
  );
  console.log(`✅ Bundled ${jsFiles.length} file(s) to js/main.min.js`);
} catch (error) {
  console.error(`❌ Failed to bundle main.min.js`);
  process.exit(1);
} finally {
  // Clean up temporary file
  if (fs.existsSync(tempEntry)) {
    fs.unlinkSync(tempEntry);
  }
}
