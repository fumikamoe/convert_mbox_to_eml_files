mbox file to eml files converter.
================================

Split huge mbox file(giga byte)  to many .eml (.emlx) file.

why?
----

Google TakeOut is great and great judge. huge thanks to google.


then. I tried export whole mails for OSX Spotlight search.

but Downloaded file is one HUGE mbox file.

so, I made this.


requiment
---------

- PHP cli (really, It is not joke)

tested with 5.5.7. over 6 giga byte file. relax.

usage
-----

```
mkdir ~/many_eml_file/
php mbox_to_eml.php ~/Downloads/aaaa.mbox ~/many_eml_file/
```

and...

for Google TakeOut's output mbox, require option below. (reason will be mentioned later.)

3rd arg is for skip line from head of section.

```
mkdir ~/many_eml_file/
php mbox_to_eml.php ~/Downloads/aaaa.mbox ~/many_eml_file/ 2
```

for osx spotlight
-----------------

osx spotlight indexing not support .eml, must use .emlx.

if you want .emlx, set 4th arg = 1.

```
mkdir ~/many_eml_file/
php mbox_to_eml.php ~/Downloads/aaaa.mbox ~/many_eml_file/ 2 1
```

(added meta data is dummy(blank). but i can search by spotlight with 10.9(mavericks) .)

hey google!
==========

wtf?

```
X-GM-THRID: xxxxxxxxxxxxxxxxxxxx
  <-- this is blank line.
Delivered-To: xxxxxxxxx@xxxxxxxx
blah:
foo:
subject:

hello i am phper!
```

I think, DON'T insert blank line before body in mbox/eml...

(I think... but... this is google product... I am wrong? Certainly, I am phper.)

blank line exist in some mail. these don't have `X-Gmail-Labels:`.


---

Originated from uzulla/convert_mbox_to_eml_files

I convert this for Laravel Service container

Thx Uzulla!

