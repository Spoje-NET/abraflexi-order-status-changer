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
