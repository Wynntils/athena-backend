<!DOCTYPE html>
<!-- Important: must specify -->
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <!-- Important: rapi-doc uses utf8 charecters -->
    <script
        type="module"
        src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"
    ></script>
    <title>Athena API Documentation</title>
</head>
<body>
<rapi-doc
    spec-url="/docs/api-docs.json"
    render-style="read"
    theme="dark"
    primary-color="#9AEB53"
    schema-style="table"
    allow-spec-url-load="false"
    allow-spec-file-load="false"
{{--    allow-server-selection="false"--}}
>
    <img
        slot="logo"
        src="https://wynntils.com/images/logo.png"
        style="width: 50px;"
    />
    <div slot="header">
        <h3>Athena API Documentation</h3>
    </div>
</rapi-doc>
</body>
</html>
