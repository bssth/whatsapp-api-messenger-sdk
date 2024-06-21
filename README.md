### Deprecated
#### The library was made quite a while ago and may be out of date. Please make a fork if you want to use it.

# API-Messenger SDK

Library making work with api-messenger.com easier

# Installation

Just download src/apimessenger.php or use Composer:
```
composer require mikechip/apimessenger
```

# Create instance

<pre>
  $api = new Mike4ip\ApiMessenger(
        '_token_' // api-messenger.com token
  );
</pre>

# Get QR code

Proxying via PHP:
```
header('Content-Type: image/png');
readfile( $api->getQRCode() );
```

Or show directly:
```
<img src="<?=$api->getQRCode();?>" />
```


# Send message

<pre>
    $result = $api->sendPhoneMessage('+12345', 'It works!');
    
    print(
        ($result['status'] == 'OK') ? 'Message sent' : 'Fail'
    );
</pre>

# Feedback

Use **Issues** to contact me
