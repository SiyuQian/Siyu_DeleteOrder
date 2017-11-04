# How to Install this extension to Magento 2
For command line lover:
1. Change current dir to Magento 2 root folder
2:
``` bash
git clone https://github.com/SiyuQian/Siyu_DeleteOrder.git app/code/Siyu/DeleteOrder
```
3. Run following commands to make changes work(in Magento 2 root folder):
``` bash
./bin/magento module:enable Siyu_DeleteOrder
./bin/magento setup:upgrade
./bin/magento setup:di:compile
```
4. Go to the Order view page to cleanup your orders!