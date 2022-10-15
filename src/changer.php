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


//$orderer = new \AbraFlexi\ObjednavkaPrijata(\Ease\Functions::cfg('DOCUMENTID'));
//print_r($orderer->getDataValue('poznam'));

$orderer = new \AbraFlexi\ObjednavkaPrijata();

$notes = $orderer->getColumnsFromAbraFlexi(['poznam'], ['limit' => 0]);

function stateExtract($noteRaw) {
    $state = null;
    if (strstr($noteRaw, ':')) {
        foreach (preg_split('/\n/', $noteRaw) as $noteLine) {
            if (stristr($noteLine, 'Stav:')) {
                $state = trim(preg_split('/: /', $noteLine, 2)[1], " \t\n\r\0\x0B'\"");
            }
        }
    }
    return $state;
}

foreach ($notes as $note) {

//    Připraveno (stavDoklObch.pripraveno)
//    Schváleno (stavDoklObch.schvaleno)
//    Částečně na cestě (stavDoklObch.castecneNaCeste)
//    Na cestě (stavDoklObch.naCeste)
//    Částečně vydáno/přijato (stavDoklObch.castVydano)
//    Vydáno/přijato (stavDoklObch.vydano)
//    Částečně hotovo (stavDoklObch.castHotovo)

    switch (stateExtract($note['poznam'])) {
        case 'Hotovo':
            $stavUzivK = 'stavDoklObch.hotovo';
        case 'Stornována':
            $stavUzivK = 'stavDoklObch.storno';
            break;
        case 'Nevyřízená':
        case 'Nevyzvednutá':
        case 'Probíhá příprava':
        case 'Vyřízena':
        case 'Vyřizuje se':
        case '':
        default:
            $stavUzivK = 'stavDoklObch.nespec';
    }

    if ($stavUzivK) {
        $orderer->stripBody();
        $orderer->setDataValue('stavUzivK', $stavUzivK);
        $result = $orderer->sync();
        $orderer->addStatusMessage('Change to ' . $stavUzivK, $result ? 'success' : 'error' );
    } else {
        $orderer->addStatusMessage(_('Order without known state'), 'warning');
        break;
    }
}
