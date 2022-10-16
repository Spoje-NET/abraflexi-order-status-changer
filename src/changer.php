<?php

/**
 * AbraFlex Order status changer.
 *
 * @author     Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 * @copyright  2022 Spoje.Net
 */
define('APP_NAME', 'AbraFlexiOrderChanger');
require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists('../.env')) {
    \Ease\Shared::singleton()->loadConfig('../.env', true);
}

foreach (['ABRAFLEXI_URL', 'ABRAFLEXI_LOGIN', 'ABRAFLEXI_PASSWORD', 'ABRAFLEXI_COMPANY', 'EASE_LOGGER'] as $cfgKey) {
    if (empty(\Ease\Functions::cfg($cfgKey))) {
        echo 'Requied configuration ' . $cfgKey . ' is not set.';
        exit(1);
    }
}

/**
 * Filter value by keyword
 * 
 * @param string $noteRaw
 * 
 * @return string
 */
function stateExtract($noteRaw) {
    $state = null;
    if (strstr($noteRaw, ':')) {
        foreach (preg_split('/\n/', $noteRaw) as $noteLine) {
            if (stristr($noteLine, \Ease\Functions::cfg('ORDER_NOTE_KEYWORD', 'Note:'))) {
                $state = trim(preg_split('/: /', $noteLine, 2)[1], " \t\n\r\0\x0B'\"");
            }
        }
    }
    return $state;
}

if ($argc > 1) {
    $docId = $argv[1];
} else {
    $docId = \Ease\Functions::cfg('DOCUMENTID');
}

try {
    $orderer = new \AbraFlexi\ObjednavkaPrijata($docId);

//  AbraFlexi States Availble:
//
//    Připraveno (stavDoklObch.pripraveno)
//    Schváleno (stavDoklObch.schvaleno)
//    Částečně na cestě (stavDoklObch.castecneNaCeste)
//    Na cestě (stavDoklObch.naCeste)
//    Částečně vydáno/přijato (stavDoklObch.castVydano)
//    Vydáno/přijato (stavDoklObch.vydano)
//    Částečně hotovo (stavDoklObch.castHotovo)

    $state = stateExtract($orderer->getDataValue('poznam'));

    switch ($state) {
        case 'Hotovo':
            $stavUzivK = 'stavDoklObch.hotovo';
            break;
        case 'Stornována':
            $stavUzivK = 'stavDoklObch.storno';
            break;
        case 'Nevyřízená':
        case 'Nevyzvednutá':
        case 'Probíhá příprava':
        case 'Vyřízena':
        case 'Vyřizuje se':
        case '':
            $stavUzivK = 'stavDoklObch.nespec';
            break;
        default:
            $orderer->addStatusMessage('ORDER_NOTE_KEYWORD "', \Ease\Functions::cfg('ORDER_NOTE_KEYWORD', 'State:') . '" not found in order note.', 'warning');
    }

    if ($orderer->getRecordID()) {

        if ($stavUzivK) {
            $orderer->stripBody();
            $orderer->setDataValue('stavUzivK', $stavUzivK);
            $result = $orderer->sync();
            $orderer->addStatusMessage('Change to ' . $stavUzivK . ' for "' . $state . '" state', $result ? 'success' : 'error' );
        } else {
            $orderer->addStatusMessage(_('Order without known state'), 'warning');
        }
    } else {
        $orderer->addStatusMessage(_('The DOCUMENTID is not specified. Aborting'), 'warning');
    }
} catch (AbraFlexi\Exception $exc) {
    echo $exc->getMessage();
    exit(1);
}
