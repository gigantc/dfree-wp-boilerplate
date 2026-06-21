/**
 * Shared JS bundling utility
 * Used by bundle-block-js.js and bundle-component-js.js
 */

const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');

/**
 * Recursively find all .js files in a directory
 * @param {string} dir - Directory to scan
 * @returns {string[]} Array of file paths
 */
function findJsFiles(dir) {
  let jsFiles = [];

  if (!fs.existsSync(dir)) {
    return jsFiles;
  }

  const entries = fs.readdirSync(dir, { withFileTypes: true });

  for (const entry of entries) {
    const entryPath = path.join(dir, entry.name);

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

/**
 * Bundle JS files from a source directory to an output directory
 * @param {Object} config - Configuration object
 * @param {string} config.sourceDir - Source directory to scan
 * @param {string} config.outputDir - Output directory for bundled files
 * @param {string} config.type - Type label for console output (e.g., 'block', 'component')
 * @param {boolean} config.isProd - Whether to build for production (no sourcemaps)
 */
async function bundleJs({ sourceDir, outputDir, type, isProd }) {
  // Create output directory if it doesn't exist
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }

  // Find all JS files
  const jsFiles = findJsFiles(sourceDir);

  if (jsFiles.length === 0) {
    console.log(`⚠️  No ${type} JavaScript files found`);
    return { successes: 0, failures: 0 };
  }

  console.log(`📦 Bundling ${jsFiles.length} ${type} JavaScript file(s)...`);

  // Build all files in parallel
  const buildPromises = jsFiles.map(jsFile => {
    const fileName = path.basename(jsFile, '.js');
    const outputFile = path.join(outputDir, `${fileName}.min.js`);

    return esbuild.build({
      entryPoints: [jsFile],
      bundle: true,
      minify: true,
      sourcemap: !isProd,
      format: 'iife',
      outfile: outputFile,
    }).then(() => ({ success: true, fileName }))
      .catch(error => ({ success: false, fileName, error }));
  });

  const results = await Promise.all(buildPromises);

  const successes = results.filter(r => r.success);
  const failures = results.filter(r => !r.success);

  console.log(`✅ Bundled ${successes.length} ${type} JS file(s) to ${path.relative(process.cwd(), outputDir)}/`);

  if (failures.length > 0) {
    failures.forEach(f => console.error(`❌ Failed to bundle ${f.fileName}.js:`, f.error));
  }

  return {
    successes: successes.length,
    failures: failures.length
  };
}

module.exports = { bundleJs, findJsFiles };
