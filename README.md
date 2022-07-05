# Руководство по настройке и установке на виртуальную машину Wordpress, Drupal и необходимое программное обеспечение.
 
###  Доступ к "Wordpress" и "Drupal" осуществляется по вводу логина и пароля с редиректом, который отрабатывает по имени пользователя     
     Реквизиты будут указаны в конфце работы команды "vagrant up"
###  Логины, пароли и имена баз данных генерируются во время работы скриптов
###  Общая папка доступна, даже если виртуальная машина была выключена или перезагружена
 
     Для РФ есть ряд ограничений, поэтому используем Tor или VPN.
     Wordpress и Drupal скачиваются из интернета, берутся актуальные версии.
     Перед началом работы, рекомендуется отключить HT (Hyper Threading) на хостовом сервере.
 
## 1. Необходимо скачать и установить Virtualbox.
    Страница загрузки: https://www.virtualbox.org/wiki/Downloads
    Пропускаем этот пункт, если Virtualbox установлен.
 
## 2. Необходимо скачать и установить Vagrant.
    Страница загрузки: https://www.vagrantup.com/downloads
    Альтернативная ссылка: https://disk.yandex.com/d/JhhYEB6sJMyz8Q
    Пропускаем этот пункт, если Vagrant установлен.
 
## 3. Проверяем список доступных box'ов
    terminal\cmd\powershell: vagrant box list
    В списке должна быть строка: ubuntu/focal64
    Если ее нет, то пункт 4.
 
## 4. Необходимо скачать и установить Vagrant box ubuntu/focal64.
    Страница загрузки: https://app.vagrantup.com/ubuntu/boxes/focal64
    Альтернативная ссылка: https://disk.yandex.com/d/Kof5KD55QNqYzw
    Установка box ubuntu/focal64 производится командой: vagrant box add ubuntu/focal64 "С:\vagrant\focal-server-cloudimg-amd64-vagrant.box"
    Где "ubuntu/focal64" - это имя
    Где "С:\vagrant\focal-server-cloudimg-amd64-vagrant.box" - путь до скаченного ранее файла box. Почему-то, на windows, vagrant не хочет добавлять образ, если путь    идет от корня диска, ему нужна папка !
    После установки проверяем наличие нашего box: vagrant box list
    Видим запись вида "ubuntu/focal64 (virtualbox, 0)" и переходим к следующему пункту.
 
## 5. Создаем папку для нашего vagrantfil'а.
    В ней создаем файл "Vagrantfile" без расширения, содержимое файла описано ниже. Для windows - файл можно редактировать блокнотом   (notepad).
    В ней создаем папку с именем "sync_config_folder", в папке "sync_config_folder" создадим еще папки с именами "wordpress", "drupal" и "apache". В эти папки мы    положим наши конфигурационные файлы.
    В папке "wordpress" нужно создать файл с именем: wp-config.php. Для windows - файл можно редактировать блокнотом (notepad).
    В папке "drupal" нужно создать файл с именем: settings.php. Для windows - файл можно редактировать блокнотом (notepad).
    В папке "apache" нужно создать файл с именем: 001_default.conf. Для windows - файл можно редактировать блокнотом (notepad).
 
    Содержимое этих файлов описано ниже.
 
## 6. Запускаем terminal\cmd\powershell, переходим в папку, созданной на этапе "5", командой cd и запускаем наш vagrantfile командой: vagrant up
    При запуске возможна ошибка по количеству памяти или доступным ядрам процессора, их можно изменить в vagrantfil'e.
    Так же необходимо проверить, что локальный порт, который указан в vagrantfil'e не занят, в противном случае порт необходимо освободить или изменить в vagrantfil'e на другой и соответственно Wordpress и Drupal будут доступны по новому порту. Так же у VM будет добавлен дополнительный сетевой интерфейс, который смотрит в локальную сеть и на web server можно будет зайти по этому адресу.
 
## 7. После успешного запуска виртуальной машины в virtualbox'e появится новая виртуальная машина с именем, которое указано настройках и будет выведена дополнительная информация. Рекомендую обратить внимание на нее. 
 
## 8. Остановить виртуальную машину можно командой: vagrant halt -f
    Удалить виртуальную машину можно командой: vagrant destroy -f
 
---------------------------------------------------------------------------------------------------------------------------------------
 
# Vagrantfile: 
### переменные
    LOCAL_HOST_PORT = "45678"                                                           #создаем переменную для проброса порта
    FRIENDLY_VM_NAME = "skillbox - web server v2"                                       #имя виртуальной машины
## stage 0
### настраиваем виртуальную машину
    Vagrant.configure("2") do |config|
     config.vm.box = "ubuntu/focal64"                                                                                    #ставим ubuntu 20.04 x64
     config.vm.hostname = "web.local"                                                                                    #задаем имя виртуальной машины
     config.vm.network "forwarded_port", guest:80, host:LOCAL_HOST_PORT                                                  #указываем порт для проброса с хостовой на виртуальную
     config.vm.synced_folder "./sync_config_folder", "/sync_config_folder", id: "sync_config_folder", automount: true    #папка с конфигами для wordpress и drupal. при смене имени, необходимо поменять его на новое в поле id и в 59 и 60 строке внести изменения
     config.vm.provision "shell", inline: "usermod -a -G vboxsf vagrant"                                                 #добавляем пользователя vagrant в группу vboxsf для доступа к папке
     config.vm.network "public_network"                                                                                  #добавляем сетевой интерфейс, который смотрит в локальную сеть
     config.vm.boot_timeout = 1800
     config.vm.provider "virtualbox" do |v|
     v.name = FRIENDLY_VM_NAME                                                                                           #понятное имя виртуальной машины в virtualbox
     v.memory = 4096                                                                                                     #изменить кол-во памяти
     v.cpus = 4                                                                                                          #изменить кол-во cpu
     v.customize ["modifyvm", :id, "--paravirtprovider", "kvm"]                                                          #тип провайдера виртуализации                 
### вначале в vagrant'e надо создать пустой dvd'rom и только потом в него можно смонтировать образ
     v.customize ["storageattach", :id, "--storagectl", "IDE", "--port", "1", "--device", "1",
     "--type", "dvddrive", "--mtype", "readonly", "--medium", "emptydrive"]
### смонтируем диск VBoxLinuxAdditions.iso для последующей его установки
     v.customize ["storageattach", :id, "--storagectl", "IDE", "--port", "1", "--device", "1",
     "--type", "dvddrive", "--mtype", "readonly", "--medium", "additions", "--forceunmount"]
    end 
## stage 1
### устанавливаем обновления и чистим систему
    config.vm.provision :shell, inline: <<-SHELL
     echo "Stage 1"
     sudo apt update                                                         #обновим информацию о пакетах
     sudo apt upgrade -y                                                     #обновим пакеты
     sudo apt-get install linux-headers-$(uname -r) build-essential dkms -y  #ставим VBoxLinuxAdditions
     sudo mkdir -p /mnt/cdrom
     sudo mount /dev/cdrom /mnt/cdrom
     cd /mnt/cdrom
     echo y | sudo sh ./VBoxLinuxAdditions.run                  
     sudo apt autoclean                                                       #удалить неиспользуемые пакеты из кэша
     sudo apt clean                                                           #очистка кэша
     sudo apt autoremove                                                      #удаление ненужных зависимостей
     sudo apt autoremove --purge
     sudo update-grub2                                                        #обновим загрузчик
    SHELL 
 ### перезагружаем виртуальную машину
    config.vm.provision :shell do |shell|
     shell.privileged = true
     shell.reboot = true
    end
## stage 2
### линкуем шарную папку, даже после перезагрузки vm
    config.vm.provision :shell, inline: <<-SHELL
     echo "Stage 2"
     sudo rm -rf /sync_config_folder                                #линкуем папку для доступа к ней после перезагрузки
     sudo ln -sfT /media/sf_sync_config_folder /sync_config_folder 
    SHELL 
## stage 3
### устанавливаем необходимые пакеты и производим их настройку, передадим переменную "LOCAL_HOST_PORT" в shell
    config.vm.provision :shell, env: {"LOCAL_HOST_PORT" => LOCAL_HOST_PORT}, inline: <<-SHELL
     echo "Stage 3"
     sudo apt install -y apache2                                                                                         #ставим apache
     sudo apt install -y libapache2-mod-php                                                                              #ставим доп. модули для apache
     sudo apt install -y php-curl php-gd php-mbstring php-xml php-xmlrpc php-soap php-intl php-zip php-sqlite3 php-cli
     sudo apt install -y php-mysql
     sudo apt install -y mysql-server                                                                                    #ставим базу данных
     sudo a2enmod rewrite                                                                                                #включим модуль rewrite для apache 
     sudo mkdir /download_content                                                                                        #создаем папку под скачиваемые файлы
     wget -O /download_content/wordpress_latest.tar.gz https://wordpress.org/latest.tar.gz                               #скачиваем wordpress (актуальный релиз)
     tar -xzf /download_content/wordpress_latest.tar.gz -C /download_content                                             #распаковываем 
     wget -O /download_content/drupal_latest.tar.gz https://www.drupal.org/download-latest/tar.gz                        #скачиваем drupal (актуальный релиз)
     tar -xzf /download_content/drupal_latest.tar.gz -C /download_content                                                #распаковываем
     mv /download_content/drupal-* /download_content/drupal                                                              #переименуем папку 
### создаем переменные для Wordpress
    myWordpressMysqlUser=wp_user_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -12 | head -n1)          #имя пользователя mysql для wodpress (имя пользователя не должно быть больше 32 символов)
    myWordpressMysqlDbName=wp_db_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)         #имя базы данных mysql для wordpress
    myWordpressMysqlPass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)                    #пароль пользователя mysql для wordpress
    myWordpressApache2Pass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w15 | head -n1)                  #пароль пользователя от вэб сервера apache
### создаем переменные для Drupal
    myDrupalMysqlUser=drupal_user_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w12 | head -n1)        #имя пользователя mysql для wodpress
    myDrupalMysqlDbName=drupal_db_id_$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)        #имя базы данных mysql для wordpress
    myDrupalMysqlPass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w30 | head -n1)                       #пароль пользователя mysql для wordpress
    myDrupalApache2Pass=$(tr -cd '[:alnum:]' < /dev/urandom | fold -w15 | head -n1)                     #пароль пользователя от вэб сервера apache
### создаем базы данных mysql
    mysql -u root -e "CREATE DATABASE $myWordpressMysqlDbName DEFAULT CHARACTER SET utf8;"                     #создаем базу данных для wordpress
    mysql -u root -e "create user $myWordpressMysqlUser@'localhost' identified by '$myWordpressMysqlPass';"    #создаем пользователя в базе данных для wordpress
    mysql -u root -e "grant all on $myWordpressMysqlDbName.* to $myWordpressMysqlUser@'localhost';"            #разрешаем ему подключение с localhost
    mysql -u root -e "flush privileges;"
    mysql -u root -e "CREATE DATABASE $myDrupalMysqlDbName DEFAULT CHARACTER SET utf8;"                        #создаем базу данных для drupal
    mysql -u root -e "create user $myDrupalMysqlUser@'localhost' identified by '$myDrupalMysqlPass';"          #создаем пользователя в базе данных для drupal
    mysql -u root -e "grant all on $myDrupalMysqlDbName.* to $myDrupalMysqlUser@'localhost';"                  #разрешаем ему подключение с localhost
    mysql -u root -e "flush privileges;" 
### переносим папки сайтов
### wordpress
    mv /download_content/wordpress /var/www/wordpress   #переместим wordpress в папку хоста
    chown -R root:www-data /var/www/wordpress/          #дадим права пользователю www-data на папку wordpress (пользователь www-data - дефолтный пользователь, под которым запущен php) 
### drupal
    sudo mkdir /var/www/drupal
    cd /download_content
### переместим drupal в папку хоста
    sudo mv drupal/* drupal/.htaccess drupal/.csslintrc drupal/.editorconfig drupal/.eslintignore drupal/.eslintrc.json drupal/.gitattributes /var/www/drupal   
### почистим за собой
    rm -rf /download_content  
### настраиваем хостинг
### загружаем пароли для пользователей Wordpress и Drupal в htpasswd для apache
    echo "$myWordpressApache2Pass" | htpasswd -c -i /etc/apache2/.htpasswd Wordpress
    echo "$myDrupalApache2Pass" | htpasswd -i /etc/apache2/.htpasswd Drupal
    rm /etc/apache2/sites-enabled/000-default.conf                                                       #удалим симлинк на дефолтовый конфиг 
### установим наш конфиг для apache
    cp /sync_config_folder/apache/001_default.conf /etc/apache2/sites-available/001_default.conf         #скопируем подготовленный конфиг для wordpress из общей папки "sync_config_folder/apache"
    chmod -X /etc/apache2/sites-available/001_default.conf                                               #уберем артибут исполняемого файла
    ln -s /etc/apache2/sites-available/001_default.conf /etc/apache2/sites-enabled/001_default.conf      #сделаем симлинк для конфигурационного файла, активные конфигурации лежат в папке "sites-enabled"
### подготовим конфигурационный файл для wordpress
    cp /sync_config_folder/wordpress/wp-config.php /var/www/wordpress/wp-config.php                      #копируем конфиг wordpress с преднастройками (шаблон)
    chmod -X /var/www/wordpress/wp-config.php                                                            #уберем артибут исполняемого файла
    wget -q -O- https://api.wordpress.org/secret-key/1.1/salt/ | grep 'define' | head >> /var/www/wordpress/wp-config.php    #получаем ключи и записываем в конфигурационный файл wordpress
    chown -R root:www-data /var/www/wordpress/wp-config.php                                              #дадим права пользователю www-data на конфиг wordpress  
### заменим переменные подключения к базе даннах на наши значения для wordpress
    sed -i 's/%example_db_name%/'$myWordpressMysqlDbName'/g' /var/www/wordpress/wp-config.php
    sed -i 's/%example_db_user_name%/'$myWordpressMysqlUser'/g' /var/www/wordpress/wp-config.php
    sed -i 's/%example_db_password%/'$myWordpressMysqlPass'/g' /var/www/wordpress/wp-config.php
### подготовим конфигурационный фвйл для drupal
    sudo cp /var/www/drupal/sites/default/default.settings.php /var/www/drupal/sites/default/settings.php
    cat /sync_config_folder/drupal/settings.php > /var/www/drupal/sites/default/settings.php        #загрузим подготовленный конфиг
    chown -R root:www-data /var/www/drupal/                                                         #дадим права пользователю www-data на папку drupal (пользователь www-data - дефолтный пользователь, под которым запущен php)
    chmod -R 755 /var/www/drupal/                                                                   #выставим права на папку drupal
    sudo mkdir /var/www/drupal/sites/default/files                                                  #создадим необходимые файла, папки и дадим им права
    sudo mkdir /var/www/drupal/sites/default/files/translations
    chmod a+w /var/www/drupal/sites/default/settings.php
    chmod a+w /var/www/drupal/sites/default/files
    chmod a+w /var/www/drupal/sites/default/files/translations
 
### заменим переменные подключения к базе даннах на наши значения для drupal
    sed -i 's/%example_db_name%/'$myDrupalMysqlDbName'/g' /var/www/drupal/sites/default/settings.php
    sed -i 's/%example_db_user_name%/'$myDrupalMysqlUser'/g' /var/www/drupal/sites/default/settings.php
    sed -i 's/%example_db_password%/'$myDrupalMysqlPass'/g' /var/www/drupal/sites/default/settings.php
 
### готовим файл с информацией о нашей vm
    echo "echo in info file"
    echo > /vagrant_up_info.txt
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
## stage 4
### перезагружаем виртуальную машину
    config.vm.provision :shell do |shell|
     shell.privileged = true
     shell.reboot = true
    end 
## stage 5
### выводим сообщение после окончания работы скрипта
    config.vm.provision "shell", inline: <<-SHELL
     sed -i 's/%ip_address_list%/'"$(hostname -I)"'/g' /vagrant_up_info.txt
     cat /vagrant_up_info.txt
    SHELL
## stage 6
    config.vm.post_up_message = <<-HEREDOC   
    VM info file after config: /vagrant_up_info.txt
    p.s. 1. не успел решить вопрос с "php core/scripts/drupal quick-start"
         2. не успел победить ввод пароля от базы данных, при настройке
          думаю это отпадет, когда решу 1 пункт 
    HEREDOC
  end
