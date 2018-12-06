# api-messenger.com SDK

Simple class makes work with api-messenger.com easier

# Create instance

<pre>
  $api = new ChatApi(
        '_token_', // api-messenger.com token
  );
</pre>

# Get QR code

Server example:
<pre>
if(isset($_GET['qr']) {
  header('Content-Type: image/png');
  readfile( $api->getQRCode() );
}
</pre>

Client example:
<pre>
  <img id="qrcode">

  <script>
  $.get('index.php', {'qr': '1'}, function(i) {
      $('#qrcode')[0].src = 'index.php?act=qr';
  });
  </script>
</pre>

# Send message

<pre>
  $result = $api->sendPhoneMessage('+12345', 'It works!');
	die(
		($result['status'] == 'OK') ? 'Message sent' : 'Fail'
	);
</pre>
