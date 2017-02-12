<?php

/**
* Простой класс чата AJAX
*/
class SimpleAjaxyChat {

    /**
    * Конструктор
    */
    function SimpleAjaxyChat() {}

    /**
    * Добавляемк введенное сообщение в базу данных
    */
    function acceptMessages() {
        $sUsername = $GLOBALS['aLMemInfo']['name'];
        $iUserID = (int)$GLOBALS['aLMemInfo']['id'];
        if($sUsername && isset($_POST['s_message']) && $_POST['s_message'] != '') {
            $sMessage = $GLOBALS['MySQL']->process_db_input($_POST['s_message'], A_TAGS_STRIP);
            if ($sMessage != '') {
                $GLOBALS['MySQL']->res("INSERT INTO `s_ajax_chat_messages` SET `member_id`='{$iUserID}', `member_name`='{$sUsername}', `message`='{$sMessage}', `when`=UNIX_TIMESTAMP()");
            }
        }
    }

    /**
    * Вводим форму ввода текста
    */
    function getInputForm() {
        ob_start();
        require_once('templates/chat_input.html');
        return ob_get_clean();
    }

    /**
    * Возвращаем 15 последних сообщений
    */
    function getMessages($bOnlyMessages = false) {
        $aMessages = $GLOBALS['MySQL']->getAll("SELECT `s_ajax_chat_messages`.*, `s_members`.`name`, UNIX_TIMESTAMP()-`s_ajax_chat_messages`.`when` AS 'diff' FROM `s_ajax_chat_messages` INNER JOIN `s_members` ON `s_members`.`id` = `s_ajax_chat_messages`.`member_id` ORDER BY `id` DESC LIMIT 15");

        $sMessages = '';
        // Выбираем сообщения из базы
        foreach ($aMessages as $iID => $aMessage) {
            $sExStyles = $sExJS = '';
            $iDiff = (int)$aMessage['diff'];
            if ($iDiff < 7) {
                $sExStyles = 'style="display:none;"';
                $sExJS = "<script> $('#message_{$aMessage['id']}').slideToggle('slow'); </script>";
            }

            $sWhen = date("H:i:s", $aMessage['when']);
            $sMessages .= '<div class="message" id="message_'.$aMessage['id'].'" '.$sExStyles.'>' . $aMessage['name'] . ': ' . $aMessage['message'] . '<span>(' . $sWhen . ')</span></div>' . $sExJS;
        }

        if ($bOnlyMessages) return $sMessages;
        return '<h3>Ajaxy Chat</h3><div class="chat_main">' . $sMessages . '</div>';
    }
}

$GLOBALS['AjaxChat'] = new SimpleAjaxyChat();

?>