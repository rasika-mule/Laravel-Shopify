# Shopify Product Importer

This Laravel application allows you to upload a CSV file and import products into your Shopify store asynchronously using Laravel queues.

---

## ⚙️ Setup

1. **Clone the Repository**
   ```bash
   git clone <your-repo-url>
   cd <your-project-directory>
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Create `.env` File and Configure Environment Variables**

   Copy `.env.example` to `.env` and update the following values:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=your_db_host
   DB_PORT=your_db_port
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password

   SHOPIFY_API_KEY=your_shopify_api_key
   SHOPIFY_STORE_DOMAIN=your_store.myshopify.com
   SHOPIFY_COLLECTION_ID=your_collection_id

   QUEUE_CONNECTION=database
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the Development Server**
   ```bash
   php artisan serve
   ```

7. **Start the Queue Worker (in a separate terminal)**
   ```bash
   php artisan queue:work
   ```

---

## 📦 Usage

1. Open your browser and visit:  
   [http://127.0.0.1:8000](http://127.0.0.1:8000)

2. Use the provided form to upload a CSV file with the following.

3. The import process will run asynchronously in the background.

4. Navigate to the **Dashboard** to view:
   - Progress updates  
   - Logs  
   - Success or failure status of each import

---

## 🛠️ Notes
- Make sure your database and queue services are running.
- Ensure correct Shopify credentials are set in `.env`.

---
