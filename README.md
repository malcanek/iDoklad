# iDoklad
PHP třída pro ulehčení požadavků na iDoklad api v1.

#Získání tokenu a následná autorizace požadavků
Pro zadání prostředí, které je vyžadováno v hlavičkách iDokladu slouží funkce setEnviroment(XApp, Version, Token). Při získávání tokenu se zavolá bez proměnné token:
```php
iDoklad::setEnviroment('Moje aplikace', 1.0);
```

Pokud již známe token, volá se funkce takto:
```php
iDoklad::setEnviroment('Moje aplikace', 1.0, 'můj tajný token');
```

Pro získání tokenu slouží funkce getToken(email, heslo). Pokud se nepodaří token získat, nebo nejsou zadané parametry s názvem aplikace a její verzí, vyhodí funkce výjimku (Exception). Ta má ve zprávě buď 'Zadejte nazev aplikace a jeji verzi' (= zavolejte funkci setEnviroment, nebo obsahuje chybu z api (např. špatné jméno a heslo). Pokud proběhne získání úspěšně, uloží token do třídy a zároveň ho vrátí pro další zpracování (např. uložení do db).
```php
iDoklad::getToken('muj@email.cz', 'mojetajneheslo');
```

#Třída iDoklad
Tato třída slouží k obsluhování api pomocí funkcí v ní vytvořených. Každá funkce je zdokumentována ohledně proměnných + obsahuje odkaz na dokumentaci api, aby bylo jasné, jaké parametry je třeba zadávat. U funkcí pro získávání PDF je třeba zadat adresu, kam se má PDF uložit.

Může se stát, že některé funkce nebudou fungovat správně, nebo tak jak by měly. Pokud tomu tak bude, můžete mne kontaktovat a pokusím se je opravit.

#Třída iDokladMini
Zjednodušená třída pro přístup k api. Obsahuje pouze funkci na získání tokenu, nastavení prostředí a následné zavolání do api. Volání do api lze provádět na libolné adresy, prefix api není nutné používat. Pro dotaz do api se volá funkce curlData(adresa, parametry GET, hlavička, parametry POST).

Adresa je odkaz do api (např. IssuedInvoices)
Parametry GET jsou parametry, které se posílají api (např. filtry). Vkládá se sem celé pole (array). Výchozí hodnota je prázdné pole.
Hlavička je hlavička značící typ dotazu, povolené jsou GET (výchozí), POST, PUT, DELETE.
Parametry POST jsou parametry určené POST a PUT příkazy. Výchozí hodnota je prázdné pole.

Příklady GET dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices');
$data = iDokladMini::curlData('IssuedInvoices', $filter);
```

Příklad POST dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices', array(), 'POST', $params);
```

Příklad PUT dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices/'.$id, array(), 'PUT', $params);
```

Příkald DELETE dotazu
```php
iDokladMini::curlData('IssuedInvoices/'.$id, array(), 'DELETE', array());
```

Třída automaticky nekontroluje zadaný token!

Třída obsahuje funkci na uložení PDF v případě jeho stažení. Tato funkce se volá base64toPDF(pdf, path). Jako první parametr se zadá base64 pdf řetězec. Jako druhý parametr se zadává adresa, kam se má PDF uložit.
# iDoklad
PHP třída pro ulehčení požadavků na iDoklad api.

#Získání tokenu a následná autorizace požadavků
Pro zadání prostředí, které je vyžadováno v hlavičkách iDokladu slouží funkce setEnviroment(XApp, Version, Token). Při získávání tokenu se zavolá bez proměnné token:
```php
iDoklad::setEnviroment('Moje aplikace', 1.0);
```

Pokud již známe token, volá se funkce takto:
```php
iDoklad::setEnviroment('Moje aplikace', 1.0, 'můj tajný token');
```

Pro získání tokenu slouží funkce getToken(email, heslo). Pokud se nepodaří token získat, nebo nejsou zadané parametry s názvem aplikace a její verzí, vyhodí funkce výjimku (Exception). Ta má ve zprávě buď 'Zadejte nazev aplikace a jeji verzi' (= zavolejte funkci setEnviroment, nebo obsahuje chybu z api (např. špatné jméno a heslo). Pokud proběhne získání úspěšně, uloží token do třídy a zároveň ho vrátí pro další zpracování (např. uložení do db).
```php
iDoklad::getToken('muj@email.cz', 'mojetajneheslo');
```

#Třída iDoklad
Tato třída slouží k obsluhování api pomocí funkcí v ní vytvořených. Každá funkce je zdokumentována ohledně proměnných + obsahuje odkaz na dokumentaci api, aby bylo jasné, jaké parametry je třeba zadávat. U funkcí pro získávání PDF je třeba zadat adresu, kam se má PDF uložit.

Může se stát, že některé funkce nebudou fungovat správně, nebo tak jak by měly. Pokud tomu tak bude, můžete mne kontaktovat a pokusím se je opravit.

#Třída iDokladMini
Zjednodušená třída pro přístup k api. Obsahuje pouze funkci na získání tokenu, nastavení prostředí a následné zavolání do api. Volání do api lze provádět na libolné adresy, prefix api není nutné používat. Pro dotaz do api se volá funkce curlData(adresa, parametry GET, hlavička, parametry POST).

Adresa je odkaz do api (např. IssuedInvoices)
Parametry GET jsou parametry, které se posílají api (např. filtry). Vkládá se sem celé pole (array). Výchozí hodnota je prázdné pole.
Hlavička je hlavička značící typ dotazu, povolené jsou GET (výchozí), POST, PUT, DELETE.
Parametry POST jsou parametry určené POST a PUT příkazy. Výchozí hodnota je prázdné pole.

Příklady GET dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices');
$data = iDokladMini::curlData('IssuedInvoices', $filter);
```

Příklad POST dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices', array(), 'POST', $params);
```

Příklad PUT dotazu
```php
$data = iDokladMini::curlData('IssuedInvoices/'.$id, array(), 'PUT', $params);
```

Příkald DELETE dotazu
```php
iDokladMini::curlData('IssuedInvoices/'.$id, array(), 'DELETE', array());
```

Třída automaticky nekontroluje zadaný token!

Třída obsahuje funkci na uložení PDF v případě jeho stažení. Tato funkce se volá base64toPDF(pdf, path). Jako první parametr se zadá base64 pdf řetězec. Jako druhý parametr se zadává adresa, kam se má PDF uložit.
