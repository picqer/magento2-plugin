### Picqer Extended Integration for Magento 2
---- 


Magento 2 Extensions for Picqer. 


---- 
### Installation: 

This project can easily be installed through Composer.

`composer require picqer/magento2-plugin`

### Activate module

1. Log onto your Magento 2 admin account and navigate to Stores > Configuration > Picqer > Webhooks
2. Fill out the general configuration information:
    + Active: Yes
    + Connection Key: can be found in Picqer > Settings > Webshops > Magento shop. Copy and paste in this field. 
    + Picqer Domain: is the prefix of your domain name. If you log on to 'test.picqer.com', then fill in 'test'. 
    
Orders will now be pushed to Picqer immediately. 
    