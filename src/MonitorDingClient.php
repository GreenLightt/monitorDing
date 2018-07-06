<?php
namespace Redgo\MonitorDing;

use Redgo\MonitorDing\Exceptions\SendErrorException;

class MonitorDingClient {

    protected $webhook;
    protected $curl_verify;

    public function __construct($webhook, $curl_verify=false)
    {
        $this->webhook = $webhook;
        $this->curl_verify = $curl_verify;
    }

    /**
     * 发送文本类型的消息
     *
     * @param $content string 消息内容
     * @param array $atMobiles 被@人的手机号
     * @param bool $isAtAll 是否 @ 所有人
     * @throws SendErrorException
     */
    public function sendText($content, $atMobiles=[], $isAtAll=false) {
        $params = [
            'msgtype' => 'text',
            'text'    => [
                'content' => $content,
            ],
            'at'      => [
                'atMobiles' => $atMobiles,
                'isAtAll'   => $isAtAll
            ]
        ];
        $this->send($params);
    }

    /**
     * 发送 Link 类型的消息
     *
     *
     * @param $title
     * @param $text
     * @param $messageUrl
     * @param string $picUrl
     * @throws SendErrorException
     */
    public function sendLink($title, $text, $messageUrl, $picUrl="") {
        $params = [
            'msgtype' => 'link',
            'link'    => [
                'text'       => $text,
                'title'      => $title,
                'messageUrl' => $messageUrl,
                'picUrl'     => $picUrl,
            ],
        ];
        $this->send($params);
    }

    /**
     * 发送 Markdown 类型的消息
     *
     * @param $title
     * @param $text
     * @param array $atMobiles
     * @param bool $isAtAll
     */
    public function sendMarkdown($title, $text, $atMobiles=[], $isAtAll=false) {
        $params = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $text,
            ],
            'at' => [
                'atMobiles' => $atMobiles,
                'isAtAll' => $isAtAll,
            ],
        ];

        $this->send($params);
    }

    /**
     * 发送
     * @param array $params 请求需要的参数
     * @throws SendErrorException
     */
    private function send($params=[]) {
        if (! config('monitorDing.enabled')) {
            app('Log')->info('~~ Monitor Ding ~~');
            app('Log')->info($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->webhook);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->curl_verify) {
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if ($data['errcode']) {
            throw new SendErrorException($data['errmsg']);
        }
    }
}