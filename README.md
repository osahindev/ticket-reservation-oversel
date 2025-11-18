# Konser Bileti Hücumu

## Çözüm Genel Bakışı
Bu proje, konser biletlerinin eşzamanlı olarak rezerve edilmesini sağlayan bir sistemdir. Laravel framework'ü kullanılarak geliştirilmiştir ve Docker ile konteynerize edilmiştir. PostgreSQL veritabanı, bilet rezervasyonları ve eşzamanlılık problemlerini çözmek için kullanılmıştır.

## Eşzamanlılık Stratejisi

Yarış durumlarını önlemek için kötümser kilitleme (`lockForUpdate`) kullanılmıştır. Bu yaklaşım, aynı anda birden fazla işlemin aynı kaydı değiştirmesini engeller. 

- **Avantajlar:** Veri tutarlılığı garanti edilir.
- **Dezavantajlar:** Kilitlenme durumlarında performans düşebilir.
- **Alternatif:**
Stok bilgileri redis üzerinde tutulabilir. Red Lock veya LUA script ile atomik güncellemeler ile daha yüksek performans alınabilecek bir yapı kurulabilir.

## Veritabanı Şeması

### Modeller ve İlişkiler

- **Event**: Bir etkinliği temsil eder.
- **Reservation**: Bir etkinlik için yapılan bilet rezervasyonunu temsil eder.
- **İlişkiler**: `Event` birden fazla `Reservation` ile ilişkilidir.

![Database Diagram](dbdiagram.png "Database Diagram")

## API Endpoint'leri

#### 1. Etkinlik Listesi

* **URL:** /api/events
* **Method:** GET
* **Açıklama:** Tüm etkinlikleri listeler
* **Örnek İstek:**

~~~bash
curl -X GET http://localhost/api/events
~~~

* **Örnek Yanıt:**

~~~json
{
    "data": [
        {
            "id": 1,
            "name": "Örnek Etkinlik",
            "ticket_quantity": 1990
        }
    ]
}
~~~

#### 2. Reservasyon Oluşturma

* **URL:** /api/reserve
* **Method:** POST
* **Açıklama:** Bir etkinlikten bilet rezervasyonu yapar.
* **Parametreler:**
  * event_id: Etkinlik ID'si
  * amount: Rezerve edilecek bilet adeti
* **Örnek İstek:**

~~~bash
curl -X POST http://localhost/api/reserve \
  -H "Content-Type: application/json" \
  -d '{"event_id": 1, "amount": 1}'
~~~

* **Örnek yanıt:**

~~~json
{
    "data": {
        "id": 4,
        "user_uid": "c9439d95-2123-4d0b-8967-50903fc0b9a8",
        "amount": 10,
        "status": "reserved",
        "expires_at": "2025-11-18T07:54:23.000000Z",
        "created_at": "2025-11-18T07:49:23.000000Z",
        "updated_at": "2025-11-18T07:49:23.000000Z"
    }
}
~~~

#### 3. Satın Alma


* **URL:** /api/purchase
* **Method:** POST
* **Açıklama:** Bekleyen rezervasyonu satın alındı durumunu günceller.
* **Parametreler:**
  * reservation_id: Rezervasyon ID'si
* **Headers:**
  * Visitor-Token: Kullanıcı için oluşturulmuş UUID.
* **Örnek İstek:**

~~~bash
curl -X POST http://localhost/api/purchase \
  -H "Content-Type: application/json" \
  -H "Visitor-Token: c9439d95-2123-4d0b-8967-50903fc0b9a8"
  -d '{"reservation_id": 1}'
~~~

* **Örnek yanıt:**

~~~json
{
    "data": {
        "id": 6,
        "user_uid": "397fbc8d-4313-49ee-96fe-b61274b7331a",
        "amount": 10,
        "status": "purchased",
        "expires_at": "2025-11-18T08:11:02.000000Z",
        "created_at": "2025-11-18T08:06:02.000000Z",
        "updated_at": "2025-11-18T08:06:09.000000Z"
    }
}
~~~

## Kurulum Talimatları

Gereksinimler:

* Docker ve Docker Compose
* PHP 8.2+
* Composer

Adımlar:

1. **Depoyu Klonlayın**

~~~bash
git clone https://github.com/osahindev/ticket-reservation-oversel.git
cd ticket-reservation-oversel
~~~

2. **Bağımlılıkları Yükleyin**

~~~bash
composer install
~~~

3. **.env Dosyasını yapılandırın**
.env.example dosyasından .env dosyası oluşturun ve bir application key oluşturun.

~~~bash
cp .env.example .env
php artisan key:generate
~~~

Veritabanı ayarlarını yapılandırın.

~~~
DB_CONNECTION=pgsql
DB_HOST=postgresql
DB_PORT=5432
DB_DATABASE=ticketing_system
DB_USERNAME=postgres
DB_PASSWORD=example
~~~

4. **Servisleri Ayağa Kaldırın**

~~~bash
docker compose build
docker compose up -d
~~~

veya 

~~~bash
docker-compose build
docker-compose up -d
~~~

5. **Veritabanını Kurun**

~~~bash
docker exec -it backend php artisan migrate --seed --force
~~~

6. **Uygulamayı Çalıştırın**

Artık uygulamaya http://localhost adresinden ulaşabilirsiniz.