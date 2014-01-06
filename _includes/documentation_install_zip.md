* Log into the server where you wish to install *Pimp My Log*

* Move to the directory where you wish to install the source (`/var/www/html` for example)

* Download and install with zip file:  

```sh
$ wget -O pml.zip {{ site.data.github.zip }} && unzip -o pml.zip && mv potsky-PimpMyLog-* PimpMyLog && rm pml.zip
```

or tarball file:

```sh
$ wget -O - {{ site.data.github.tar }} | tar xzf - && mv potsky-PimpMyLog-* PimpMyLog
```


* Go it! *Pimp My Log* is available at <http://server_ip/PimpMyLog/>

