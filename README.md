# E-Commerce Backend POC - Symfony 7.4.x

Ein Proof of Concept für ein E-Commerce Backend basierend auf Symfony 7.4.x mit Docker-Containerisierung und CI/CD-Pipeline.

---

## Projektübersicht

Dieses Projekt stellt ein vollständiges E-Commerce Backend mit folgenden Features bereit:

- Benutzerverwaltung (Registrierung, Verifikation, Login)  
- Produktmanagement (CRUD-Operationen für Administratoren)  
- Warenkorb-Funktionalität  
- Checkout-Prozess mit Adress- und Zahlungsverwaltung  
- Rollenbasierte Zugriffskontrolle (Customer & Admin)  
- Sicherheitsfeatures (HIBP Password Validation, Security Headers)  

---

## Voraussetzungen

- Docker & Docker Compose  
- PHP 8.4  
- Composer  

---

## Installation

### Repository klonen
```bash
git clone <repository-url>
cd e-commerce-poc
```

Umgebungsvariablen konfigurieren:
```bash
cp .env.example .env
# Bearbeiten Sie .env mit Ihren spezifischen Konfigurationen
```

Docker Container starten:
```bash
docker-compose up -d
```

Abhängigkeiten installieren:
```bash
docker-compose exec web composer install
```

Datenbank einrichten:
```bash
docker-compose exec web php bin/console doctrine:schema:create
```

Anwendung aufrufen

- Backend: http://localhost:8080

- phpMyAdmin: http://localhost:8081

## Docker Services

| Service    | Beschreibung                   |
| ---------- | ------------------------------ |
| web        | Apache Web Server mit PHP 8.4  |
| db         | MariaDB 10.4 Datenbank         |
| phpmyadmin | Datenbank Management Interface |

## Verzeichnisstruktur

```bash
├── config/             # Symfony Konfiguration
├── public/             # Öffentliche Dateien (inkl. OpenAPI Spec)
├── src/
│   ├── Controller/     # API Controller
│   ├── Entity/         # Doctrine Entities
│   ├── EventSubscriber/# Event Subscriber (Security Headers)
│   └── Tests/          # Test Cases
├── docker/             # Docker Konfiguration
└── .github/workflows/  # CI/CD Pipeline
```

## API Endpoints
Endpoint	Methode	Beschreibung

Öffentliche Endpoints

| Endpoint                             | Methode | Beschreibung          |
| ------------------------------------ | ------- | --------------------- |
| `/public/user`                       | POST    | Benutzerregistrierung |
| `/public/user/{token}`               | PATCH   | Account-Verifikation  |
| `/public/product/dashboard`          | GET     | Produktübersicht      |
| `/public/product/{slug}/{productId}` | GET     | Produktdetails        |
| `/public/product/search`             | GET     | Produktsuche          |
| `/public/upload`                     | POST    | Bild-Upload           |
| `/login`                             | POST    | Benutzer-Login        |

Authentifizierte Endpoints (Customer)

| Endpoint                 | Methode  | Beschreibung                     |
| ------------------------ | -------- | -------------------------------- |
| `/auth/cart`             | POST     | Produkt zum Warenkorb hinzufügen |
| `/auth/cart/list`        | GET      | Warenkorb anzeigen               |
| `/auth/cart/{productId}` | DELETE   | Produkt aus Warenkorb entfernen  |
| `/auth/deliveryAddress`  | POST     | Lieferadresse hinzufügen         |
| `/auth/paymentMethod`    | POST     | Zahlungsmethode hinzufügen       |
| `/auth/checkout-data`    | GET/POST | Checkout-Daten verwalten         |

Admin Endpoints

| Endpoint                     | Methode      | Beschreibung                 |
| ---------------------------- | ------------ | ---------------------------- |
| `/auth/admin`                | PATCH        | Benutzer zum Admin befördern |
| `/admin/product`             | POST         | Produkt erstellen            |
| `/admin/product/{productId}` | PATCH/DELETE | Produkt bearbeiten/löschen   |
| `/admin/status/{productId}`  | PATCH        | Produktstatus ändern         |


## Test-Journeys

Das Projekt enthält zwei umfassende Test-Journeys:

- Admin Journey: Registrierung → Verifikation → Login → Admin-Beförderung → Produktmanagement

- Customer Journey: Registrierung → Verifikation → Login → Produktbrowsing → Warenkorb → Checkout

Tests ausführen:
```bash
docker-compose exec web php bin/phpunit
```

## Password Validation

- HIBP Integration: Passwörter werden gegen bekannte Data Breaches geprüft

- Mindestlänge: 12 Zeichen

- Beispiel abgelehnte Passwörter: 12345Password, password123

## Security Headers

- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Content-Security-Policy`
- `Strict-Transport-Security`
- `Cross-Origin Policies`


## CI/CD Pipeline
Die GitHub Actions Pipeline führt automatisch aus:

- PHP 8.4 Setup mit erforderlichen Extensions

- Datenbank-Initialisierung (MariaDB 10.4)

- Schema Creation & Cache Clearing

- Test Execution (Admin & Customer Journeys)

- PHPUnit Test Suite

## Datenbank
Wichtige Entities

- User: Benutzerkonten

- Product: Produktinformationen

- Cart & CartItem: Warenkorb-Funktionalität

- AddressEntity: Lieferadressen

- Payment: Zahlungsmethoden

- UserTokens: Verifikationstokens

## Datenbank-Zugriff
- Host: db (Docker) oder localhost:3307 (Host)

- Datenbank: e-commerce-project

- User: symfony

- Password: symfony

## API Dokumentation
Die vollständige API-Dokumentation ist verfügbar in der Datei openapi.json mit entsprechenden Request- und Response-Beispielen.
