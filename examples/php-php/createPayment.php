<?php
	//пример createPayment.php
	//скрипт создания платежа
	
	//подключаем библиотеку
	require_once __DIR__.'/MF2Pay.php';
	$client = new MF2PayClient();
	//формируем массив с данными о платеже
	//эти данные - пример, заполните на свои
	$paymentData = array(
		'PaymentID'   => 'P2857',
		'SellerID'    => '8703DXP',
		'Amount'      => 10,
		'Email'       => 'user@inbox.ru',
		'CheckURL'    => 'http://example.com/checkPayment.php',
		'CheckMethod' => 'POST'
	);
	//описание полей:
	//PaymentID - ID платежа для идентификации платежа в системе продавца. Заполняете вы, значение должно быть уникально для каждого платежа.
	//SellerID - ваш ID в системе MF2Pay, найти можно в личном кабинете.
	//Amount - сумма платежа в mfc. Примеры: 10 или 10.00000000. Округляется до 8-ого знака после запятой. Существует минимально и максимально возможное значение, узнать его можно в FAQ для партнеров.
	//Email - email покупателя, который должен будет оплатить платеж. На этот email будет выслано сообщение о том, что платеж создан, а также по завершении платежа на этот email будет отправлена ссылка на "получение товара" - читайте в документации
	//CheckURL - URL, по которому покупатель будет направлен после успешной оплаты. По этому URL должен находиться ваш скрипт проверки оплаты.
	//CheckMethod - метод перехода на CheckURL. Может быть: POST или GET.
	
	//ограничения по типу и длине полей:
	//PaymentID - до 24 символов.
	//Amount - дробное число с 8 возможными знаками после запятой.
	//Email - по стандарту до 320 символов.
	//CheckURL - до 120 символов.
	//CheckMethod - только POST или GET.
	
	//приватный ключ продавца. получить можно в личном кабинете, у каждого продавца свой
	$key = "HpN78vB3dZx120jGmno4LpFhhCD3DSuT";
	//ключ нужен для заверения платежа
	
	//создаем платеж и получаем ответ
	//вернет false или ассоциативный массив с данными для доступа продавца к платежу.
	$result = $client->create($paymentData, $key);
	if($result == false) {
		die("Произошла ошибка при создании платежа");
	} else {
		//id - нужен для того, чтобы попасть на страницу платежа
		$paymentID = $result['id'];
		//code - нужен для проверки платежа, чтобы выдать товар покупателю или просто отметить, что платеж завершен - на стороне продавца. Как это работает описано в документации
		$paymentCode = $result['code'];
		//code необходимо записать и никому не разглашать. code соответствует конкретно данному платежу
	}
	
	//можем записать в БД сведения, чтобы затем, когда покупатель вернется, выдать ему товар или просто отметить его
	//подключаемся к БД, данные для примера
	$db_host = 'localhost';
	$db_user = 'root';
	$db_pass = '';
	$db_name = 'testbase';
	
	$db = mysql_connect($db_host, $db_user, $db_pass) or die(mysql_error());
	mysql_select_db($db_name, $db);
	
	//записываем в БД. Пример
	mysql_query("INSERT INTO payments (payID,paySID,code) VALUES ('".$paymentData['PaymentID']."', '$paymentID', '$paymentCode')", $db);
	//где:
	//payID  - varchar(24)
	//paySID - varchar(24)
	//code   - varchar(24)
	
	//если это, к примеру, проект какой-либо игры, то игра может обращаться к данному скрипту, расположенному на вашем сервере. Тогда здесь можно добавить возврат id платежа, чтобы дать покупателю ссылку на него.
	//ссылка на платеж формируется следующим образом:
	//$paymentURL = "http://mf2pay.in/payment?id=".$paymentID;
	