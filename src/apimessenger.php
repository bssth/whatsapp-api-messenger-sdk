<?php
    namespace Mike4ip;

    /**
     * Class ApiMessenger
     * @package Mike4ip
     */
    class ApiMessenger
    {
        /**
         * @var string
         */
        protected $token;

        /**
         * @var string
         */
        protected $url = '';

        /**
         * ApiMessenger constructor.
         * @param string $token
         * @param string $url
         */
        public function __construct(string $token, string $url = 'https://app.api-messenger.com')
        {
            $this->token = $token;
            $this->url = $url;
        }

        /**
         * @param $method
         * @param array $args
         * @return string
         */
        public function createUrl(string $method, array $args = []): string
        {
            $args['token'] = $this->token;
            return $this->url.'/'.$method.'?'.http_build_query($args);
        }

        /**
         * @param string $method
         * @param array $args
         * @param string $qmethod
         * @return string
         * @throws ApiMessengerException
         */
        public function query(string $method, array $args, string $qmethod = 'GET'): string
        {
            $ch = curl_init();
            $url = $this->createUrl($method);

            if($qmethod == "POST" && isset($args) && is_array($args)) {
                $content = json_encode($args);

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($content)
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            } elseif($qmethod == "GET" && isset($args) && is_array($args)) {
                $url = $this->createUrl($method, $args);
                curl_setopt($ch, CURLOPT_URL, $url);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            if(strlen($error = curl_error($ch)))
                throw new ApiMessengerException($error);

            curl_close($ch);
            return $result;
        }

        /**
         * @param int $offset
         * @return array
         * @throws ApiMessengerException
         */
        public function getInbox(int $offset = 0): array
        {
            $inbox = json_decode($this->query('messages', ($offset > 0) ? ['page' => $offset] : ['new' => 1]), 1);

            if(!isset($inbox['status']) || $inbox['status'] != "OK")
                throw new ApiMessengerException('Cannot read inbox. Result: ' . var_export($inbox, true));

            $newOffset = $inbox['pager']['currentPage']+1;
            $mess = $inbox['messages'];
            $inbox = [];

            foreach($mess as $val) {
                $val['offset'] = $newOffset;
                $inbox[] = $val;
            }

            usort($inbox, function ($a, $b) {
                if ($a['timestamp'] == $b['timestamp'])
                    return 0;
                return ($a['timestamp'] < $b['timestamp']) ? -1 : 1;
            });

            return $inbox;
        }

        /**
         * @param string $author
         * @return array
         * @throws ApiMessengerException
         */
        public function getChatMessages(string $author): array
        {
            $ib = $this->getInbox();
            $msgs = [];

            foreach($ib as $message) {
                if(isset($author) && $author == $message['chatId'])
                    $msgs[] = $message;
            }

            return $msgs;
        }

        /**
         * @param string $chat
         * @param string $text
         * @return array
         * @throws ApiMessengerException
         */
        public function sendPhoneMessage(string $chat, string $text)
        {
            return json_decode($this->query('sendmessage', [['chatId' => $chat.'@c.us', 'message' => $text]], "POST"), 1);
        }

        /**
         * @return array
         * @throws ApiMessengerException
         */
        public function getWebhook(): array
        {
            return json_decode($this->query('webhook', []), true);
        }

        /**
         * @return string
         * @throws ApiMessengerException
         */
        public function getQRCode(): string
        {
            return base64_decode(
                $this->queryGo()['img']
            );
        }

        /**
         * @return array
         * @throws ApiMessengerException
         */
        public function queryGo(): array
        {
            return json_decode(
                $this->query('go', []),
                true
            );
        }

        /**
         * @param $chat
         * @param $body
         * @param $filename
         * @param $caption
         * @return mixed
         * @throws ApiMessengerException
         */
        public function sendFile(string $chat, string $body, string $filename, string $caption)
        {
            return json_decode($this->query('sendFile', ['chatId' => $chat, 'caption' => $caption, 'filename' => $filename, 'body' => $body], 'POST'), 1);
        }

        /**
         * @param string $chat
         * @param string $text
         * @return string
         * @throws ApiMessengerException
         */
        public function sendMessage(string $chat, string $text): string
        {
            return json_decode($this->query('sendmessage', [['chatId' => $chat, 'message' => $text]], "POST"), 1)['status'];
        }
    }
