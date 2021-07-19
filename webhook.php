<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once('./LINEBotTiny.php');

$channelAccessToken = 'xxxxx';
$channelSecret = 'xxxxx';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

//LINEBotTinyファイル内のparseEventsメソッドで取得したデータを$eventとして回す
foreach ($client->parseEvents() as $event) {
	switch ($event['type']) {
		case 'message':
			//event->message->id,type,title,adress,latitude,longitudeという配列の構造
			$message = $event['message'];
			//$event->message->type->textやimageやlocationという配列の構造
			switch ($message['type']) {
				case 'location': //位置情報が送信された場合は下記処理を実行
					$lat = $message['latitude'];
					$lng = $message['longitude'];
					$url = 'http://webservice.recruit.co.jp/hotpepper/gourmet/v1/?key=e97210288f59c4af&lat=' . $lat . '&lng=' . $lng . '&format=json';
					//セッション初期化
					$ch = curl_init();
					//curlオプションを設定
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					//curlを実行して、その内容を変数に格納
					$response = curl_exec($ch);
					//jsonエンコードをPHPの変数に変換する
					$json = json_decode($response, true);
					$shops = $json['results']['shop']; //$jsonで取得したデータの配列内のデータを絞り込み
					//さらにshopの配列内の取り出したいデータを取得
					foreach ($shops as $key => $value) {
						$results .= "■";
						$results .= $value['name'] . "\n";
						$results .= $value['urls']['pc'] . "\n\n";
						if ($key == 3) {
							break;
						}
					}
					//送信元にリプライ
					$client->replyMessage([
						'replyToken' => $event['replyToken'],
						'messages' => [
							[
								'type' => 'text',
								'text' => $results
							]
						]
					]);
					break;
				case 'text':
					$client->replyMessage([
						'replyToken' => $event['replyToken'],
						'messages' => [
							[
								'type' => 'text',
								'text' => 'こんにちは！位置情報を送信してお店を検索してみましょう♪'
							]
						]
					 ]);
					break;
				default:
					error_log('Unsupported message type: ' . $message['type']);
					break;
			}
			break;
		default:
			error_log('Unsupported event type: ' . $event['type']);
			break;
	}
};
?>

