# Multibot2 #

Multibot przeznaczony dla serwerów TeamSpeak 3.

### Zalety ###

* Skalowalność
* Wydajność 
* Możliwość uruchomienia nieskończonej ilości instancji (Procesów)
* Obsługa komend z czatu serwerowego oraz prywatnej wiadomości
* System permisji komend
* Łatwość dodawania nowych komend
* Dynamiczne włączanie oraz wyłączanie pożądanych funkcji (Brak implementacji)

### Ważne informacje ###

* Multibot działa tylko w systemie linux z racji zastosowania wewnętrznego socketu
* Poradniki dotyczące konfiguracji itp. możesz znaleźć na exus.ovh
* Cały system multibota opiera się na ts3admin.class

## Konfiguracja ##

1. Pobierz repozytorium na swój serwerwer
2. Edytuj odpowiednio plik core.ini z folderu "Configs"
3. Uruchom rdzeń bota plikiem bot.sh z parametrem "start" (./bot.sh start)