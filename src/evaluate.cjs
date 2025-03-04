var Module = require("module").Module, nodePath = require("path"), appModulePaths = [], old_nodeModulePaths = Module._nodeModulePaths; function addPath(a, o) { function e(o) { a = nodePath.normalize(a), o && -1 === o.indexOf(a) && o.push(a) } if (a = nodePath.normalize(a), -1 === appModulePaths.indexOf(a)) for (appModulePaths.push(a), require.main && e(require.main.paths), o = o || module.parent; o && o !== require.main;)e(o.paths), o = o.parent } Module._nodeModulePaths = function (a) { var o = old_nodeModulePaths.call(this, a); return o.concat(appModulePaths) };
addPath(process.cwd() + '/node_modules');

var input = '';

process.stdin.on('data', function (chunk) {
    input += chunk;
});

process.stdin.on('end', async function () {
    var payload = JSON.parse(input);
    const call = (await import(payload.path)).default;
    const value = await (call)(...payload.args);

    var output = {
        result: value
    };

    process.stdout.write(payload.delineator);
    process.stdout.write(JSON.stringify(output));
});
