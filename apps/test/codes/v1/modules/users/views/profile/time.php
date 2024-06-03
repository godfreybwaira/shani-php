<h1>Time is now</h1>

<script type="text/javascript">
//    let ws = new WebSocket('ws://dev.shani.v2.local:8008/');
    ws.onopen = (e) => {
        ws.send('from http');
        console.log('connected.', e);
    };
    ws.onmessage = (e) => {
        console.log('message.', e);
    };
    ws.onclose = (e) => {
        console.log('close.', e);
    };
    ws.onerror = (e) => {
        console.log('error.', e);
    };
</script>