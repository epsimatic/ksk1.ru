ksk1.ru
=======

Общие библиотеки «Инфотек»

* **header.html, footer.html** — общие шапка и подвал
* **style.css** — общий стиль
* **libs/** — библиотеки
* *TODO: дописать*


## Развёртывание ksk1.ru:

<pre>
su www-data -c 'git clone https://github.com/glgc25/ksk1.ru.git .'
su www-data -c 'git submodule update --init'
</pre>

или

<pre>
sudo -u www-data -- git clone https://github.com/glgc25/ksk1.ru.git .
sudo -u www-data -- git submodule update --init
</pre>