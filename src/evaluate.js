var input = '';

process.stdin.on('data', function (chunk) {
    input += chunk;
});

process.stdin.on('end', function () {
    var payload = JSON.parse(input);

    var output = {
        result: require(payload.path)(...payload.args)
    };

    process.stdout.write(payload.delineator);
    process.stdout.write(JSON.stringify(output));
});
