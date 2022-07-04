# -*- mode: ruby -*-
# vi: set ft=ruby :

LOCAL_HOST_PORT = "45678"
FRIENDLY_VM_NAME = "skillbox - web server v2"

#stage 0

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/focal64"
  config.vm.hostname = "web.local"
  config.vm.network "forwarded_port", guest:80, host:LOCAL_HOST_PORT
  config.vm.synced_folder "./sync_config_folder", "/sync_config_folder", id: "sync_config_folder", automount: true
  config.vm.provision "shell", inline: "usermod -a -G vboxsf vagrant"
  config.vm.network "public_network"
  config.vm.boot_timeout = 1800
  config.vm.provider "virtualbox" do |v|
     v.name = FRIENDLY_VM_NAME
     v.memory = 1024
     v.cpus = 1
     v.customize ["modifyvm", :id, "--paravirtprovider", "kvm"]
     v.customize ["storageattach", :id, "--storagectl", "IDE", "--port", "1", "--device", "1",
                    "--type", "dvddrive", "--mtype", "readonly", "--medium", "emptydrive"]
       v.customize ["storageattach", :id, "--storagectl", "IDE", "--port", "1", "--device", "1",
                      "--type", "dvddrive", "--mtype", "readonly", "--medium", "additions", "--forceunmount"]
         end

#stage 1

  config.vm.provision :shell, inline: <<-SHELL
    echo "Stage 1"
    sudo apt update
    sudo apt upgrade -y
    sudo apt-get install linux-headers-$(uname -r) build-essential dkms -y
    sudo mkdir -p /mnt/cdrom
    sudo mount /dev/cdrom /mnt/cdrom
    cd /mnt/cdrom
    echo y | sudo sh ./VBoxLinuxAdditions.run
    sudo apt autoclean
    sudo apt clean
    sudo apt autoremove
    sudo apt autoremove --purge
    sudo update-grub2

  SHELL

#stage 2

  config.vm.provision :shell, inline: <<-SHELL
    echo "Stage 2"
    sudo rm -rf /sync_config_folder
    sudo ln -sfT /media/sf_sync_config_folder /sync_config_folder

  SHELL

#stage3

  config.vm.provision :shell, env: {"LOCAL_HOST_PORT" => LOCAL_HOST_PORT}, inline: <<-SHELL
    echo "Stage 3"
    sudo apt install -y apache2
    sudo apt install -y libapache2-mod-php
    sudo apt install -y php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip php-sqlite3 php-cli
    sudo apt install -y php-mysql
    sudo apt install -y mysql-server
    sudo a2enmod rewrite
    sudo mkdir /download_content
    wget -O /download_content/wordpress_latest.tar.gz https://wordpress.org/latest.tar.gz
    tar -xzf /download_content/wordpress_latest.tar.gz -C /download_content
    wget -O /download_content/drupal_latest.tar.gz https://www.drupal.org/download-latest/tar.gz
    tar -xzf /download_content/drupal_latest.tar.gz -C /download_content
    mv /download_content/drupal-* /download_content/drupal
    myWordpressMysqlUser=wp_user_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -12 | head -n)
    myWordpressMysqlDbName=wp_db_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)
    myWordpressMysqlPass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)
    myWordpressApache2Pass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w15 | head -n1)
    myDrupalMysqlUser=drupal_user_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w12 | head -n1)
    myDrupalMysqlDbName=drupal_db_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)
    myDrupalMysqlPass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)
    myDrupalApache2Pass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w15 | head -n1)
    mysql -u root -e "CREATE DATABASE $myWordpressMysqlDbName DEFAULT CHARACTER SET utf8;"
    mysql -u root -e "create user $myWordpressMysqlUser@'localhost' identified by '$myWordpressMysqlPass';"
    mysql -u root -e "grant all on $myWordpressMysqlDbName.* to $myWordpressMysqlUser@'localhost';"
    mysql -u root -e "flush privileges;"
    mysql -u root -e "CREATE DATABASE $myDrupalMysqlDbName DEFAULT CHARACTER SET utf8;"
    mysql -u root -e "create user $myDrupalMysqlUser@'localhost' identified by '$myDrupalMysqlPass';"
    mysql -u root -e "grant all on $myDrupalMysqlDbName.* to $myDrupalMysqlUser@'localhost';"
    mysql -u root -e "flush privileges;"
    mv /download_content/wordpress /var/www/wordpress
    chown -R root:www-data /var/www/wordpress/
    sudo mkdir /var/www/drupal
    cd /download_content
    sudo mv drupal/* drupal/.htaccess drupal/.csslintrc drupal/.editorconfig drupal/.eslintignore drupal/.eslintrc.json drupal/.gitattributes /var/www/drupal
    rm -rf /download_content
    echo "$myWordpressApache2Pass" | htpasswd -c -i /etc/apache2/.htpasswd Wordpress
    echo "$myDrupalApache2Pass" | htpasswd -i /etc/apache2/.htpasswd Drupal

    rm /etc/apache2/sites-enabled/000-default.conf
    cp /sync_config_folder/apache/001_default.conf /etc/apache2/sites-available/001_default.conf
    chmod -X /etc/apache2/sites-available/001_default.conf
    ln -s /etc/apache2/sites-available/001_default.conf /etc/apache2/sites-enabled/001_default.conf
    cp /sync_config_folder/wordpress/wp-config.php /var/www/wordpress/wp-config.php
    chmod -X /var/www/wordpress/wp-config.php
    wget -q -O- https://api.wordpress.org/secret-key/1.1/salt/ | grep 'define' | head >> /var/www/wordpress/wp-config.php
    chown -R root:www-data /var/www/wordpress/wp-config.php

    sed -i 's/%example_db_name%/'$myWordpressMysqlDbName'/g' /var/www/wordpress/wp-config.php
    sed -i 's/%example_db_user_name%/'$myWordpressMysqlUser'/g' /var/www/wordpress/wp-config.php
    sed -i 's/%example_db_password%/'$myWordpressMysqlPass'/g' /var/www/wordpress/wp-config.php

    sudo cp /var/www/drupal/sites/default/default.settings.php /var/www/drupal/sites/default/settings.php
    cat /sync_config_folder/drupal/settings.php > /var/www/drupal/sites/default/settings.php
    chown -R root:www-data /var/www/drupal/
    chmod -R 755 /var/www/drupal/
    sudo mkdir /var/www/drupal/sites/default/files
    sudo mkdir /var/www/drupal/sites/default/files/translations
    chmod a+w /var/www/drupal/sites/default/settings.php
    chmod a+w /var/www/drupal/sites/default/files
    chmod a+w /var/www/drupal/sites/default/files/translations
    sed -i 's/%example_db_name%/'$myDrupalMysqlDbName'/g' /var/www/drupal/sites/default/settings.php
    sed -i 's/%example_db_user_name%/'$myDrupalMysqlUser'/g' /var/www/drupal/sites/default/settings.php
    sed -i 's/%example_db_password%/'$myDrupalMysqlPass'/g' /var/www/drupal/sites/default/settings.php

     echo "echo in info file"
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo __________________________________________________________________________________________ >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Skillbox intensive homework - Web sites Wordpress and Drupal>> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Available ip address on VM: %ip_address_list% >> /vagrant_up_info.txt
     echo Sites available on localhost: http://localhost:$LOCAL_HOST_PORT >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Drupal MySQL database name: $myDrupalMysqlDbName >> /vagrant_up_info.txt
     echo Drupal MySQL database user: $myDrupalMysqlUser >> /vagrant_up_info.txt
     echo Drupal MySQL database password: $myDrupalMysqlPass >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Wordpress MySQL database name: $myWordpressMysqlDbName >> /vagrant_up_info.txt
     echo Wordpress MySQL database user: $myWordpressMysqlUser >> /vagrant_up_info.txt
     echo Wordpress MySQL database password: $myWordpressMysqlPass >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Apache user for Drupal instants: Drupal >> /vagrant_up_info.txt
     echo Apache user password for Drupal instants: $myDrupalApache2Pass >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo Apache user for Wordpress instants: Wordpress >> /vagrant_up_info.txt
     echo Apache user password for Wordpress instants: $myWordpressApache2Pass >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo __________________________________________________________________________________________ >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
     echo >> /vagrant_up_info.txt
  SHELL

  config.vm.provision :shell do |shell|
     shell.privileged = true
     shell.reboot = true
   end

#stage 5

  config.vm.provision "shell", inline: <<-SHELL
    sed -i 's/%ip_address_list%/'"$(hostname -I)"'/g' /vagrant_up_info.txt
    cat /vagrant_up_info.txt
  SHELL

#stage 6

   config.vm.post_up_message = <<-HEREDOC


    VM info file after config: /vagrant_up_info.txt

   HEREDOC

end




















