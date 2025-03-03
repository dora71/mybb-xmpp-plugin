# mybb-xmpp-plugin

Dieses Plugin informiert Dich über folgende Geschehnisse in Deinem MyBB Forum via XMPP:

  * Login in das Forum
  * Login in das AdminCP
  * Login in das ModCP
  * erfolgreiche Registrierung eines neuen Benutzers
  * Veröffentlichung eines neuen Themas
  * Veröffentlichung eines neuen Kalendereintrags

Dabei kann ausgewählt werden, ob die Veröffentlichungen (Thema und Kalendereintrag) an einen Benutzer oder an eine Benutzergruppe (MUC) gesendet werden soll.

## Voraussetzungen
Eine installierte Version von ```go-sendxmpp```, die Homepage mit Installationsanleitungen findet man unter [https://salsa.debian.org/mdosch/go-sendxmpp](https://salsa.debian.org/mdosch/go-sendxmpp)
Das Plugin erwartet go-sendxmpp unter ```/usr/bin/go-sendxmpp```

Desweiteren ist es ratsam, ein eigenes XMPP-Konto für die Benachrichtigungen anzulegen.

## TO-DOs
* Übersetzung in andere Sprachen