
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body style="padding:0px;">


<script>
    const myFrame = document.getElementById('myFrame');
    function reloadIframe() {
        myFrame.src = myFrame.src; // This reloads the iframe by setting its src again
    }
    // Reload the iframe every 10 seconds (10000 milliseconds)
    setInterval(reloadIframe, 5000);
</script>

<a>daily sales</a> | <a>stock report </a>

<iframe title="Adventure Works TEST" id="myFrame" width="100%" height="1200" src=https://app.powerbi.com/reportEmbed?reportId=36d08a4a-24f4-4a5c-9149-cc04c1e3eb57&autoAuth=true&ctid=373e7b4a-ab27-4ae2-a56d-b2e26fb098d0 allowFullScreen="true">
</iframe>

</body>
</html>