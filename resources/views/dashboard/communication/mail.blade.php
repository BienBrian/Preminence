<body style="padding: 2em; background-color: #f8f9fe;">
    <div style="padding:2em; background-color: #fff; border-radius: 2px;">
        <h3 style="color: #5e72e4;">Hi, {{ $name }}</h3>
        {!! strip_tags($mes, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><div><span><hr>') !!}
    </div>
</body>
