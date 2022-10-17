![Logo](abraflexi-order-status-changer.svg?raw=true)

AbraFlexi Order Status changer
==============================

Change AbraFlexi Order Status according the keywork in "note" field.


Installation
------------

Main DEB based distributions please use repo:

```shell
sudo apt install lsb-release wget apt-transport-https bzip2

wget -qO- https://repo.vitexsoftware.com/keyring.gpg | sudo tee /etc/apt/trusted.gpg.d/vitexsoftware.gpg
echo "deb [signed-by=/etc/apt/trusted.gpg.d/vitexsoftware.gpg]  https://repo.vitexsoftware.com  $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo apt update

sudo apt install abraflexi-order-status-changer
```

![Deb installation](debian/debinst.png?raw=true)


Usage
-----

Set Environment variables and/or `/etc/abraflexi-order-status-changer/.env` file.

```
ABRAFLEXI_URL=https://demo.flexibee.eu:5434
ABRAFLEXI_LOGIN=winstrom
ABRAFLEXI_PASSWORD=winstrom
ABRAFLEXI_COMPANY=demo_de
EASE_LOGGER=syslog|console

ORDER_NOTE_KEYWORD=Stav:
STAV_DOKL_OBCH_NESPEC=n/a
STAV_DOKL_OBCH_PRIPRAVENO=Pripraveno
STAV_DOKL_OBCH_SCHVALENO=Schvaleno
STAV_DOKL_OBCH_CASTECNENACESTE=Castecne na ceste
STAV_DOKL_OBCH_NACESTE=Na Ceste
STAV_DOKL_OBCH_CASTVYDANO=Částečně vydáno
STAV_DOKL_OBCH_VYDANO=Vydáno
STAV_DOKL_OBCH_CASTHOTOVO=Částečně hotovo, č. hotovo
STAV_DOKL_OBCH_HOTOVO=Hotovo
STAV_DOKL_OBCH_STORNO=Storno
```

Then run:

`abraflexi-order-status-changer code:00000003/22-1'

Or specify the document ID as environment variable or .env file DOCUMENTID value:

`DOCUMENTID=code:00000003/22-1 abraflexi-order-status-changer`

Tool load order by given DOCUMENTID and serch it's note field for line begins with `ORDER_NOTE_KEYWORD`
then change Order's field **stavUzivK** accordingly


Notge: Tool is ready to be run by [AbraFlexi Changes processor](https://github.com/VitexSoftware/abraflexi-changes-processor)
