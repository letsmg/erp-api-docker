// MongoDB initialization script
db = db.getSiblingDB('erp_api');

// Create collections with indexes
db.createCollection('users');
db.createCollection('clients');
db.createCollection('products');
db.createCollection('sales');
db.createCollection('categories');
db.createCollection('suppliers');

// Create indexes for better performance
db.users.createIndex({ "email": 1 }, { unique: true });
db.users.createIndex({ "name": 1 });
db.users.createIndex({ "created_at": 1 });

db.clients.createIndex({ "user_id": 1 });
db.clients.createIndex({ "document_number": 1 }, { unique: true, sparse: true });
db.clients.createIndex({ "name": 1 });

db.products.createIndex({ "slug": 1 }, { unique: true });
db.products.createIndex({ "barcode": 1 }, { sparse: true });
db.products.createIndex({ "description": "text", "brand": "text", "model": "text" });
db.products.createIndex({ "category_id": 1 });
db.products.createIndex({ "supplier_id": 1 });
db.products.createIndex({ "is_active": 1 });
db.products.createIndex({ "created_at": 1 });

db.sales.createIndex({ "client_id": 1 });
db.sales.createIndex({ "created_at": 1 });
db.sales.createIndex({ "total_amount": 1 });

db.categories.createIndex({ "name": 1 }, { unique: true });
db.categories.createIndex({ "slug": 1 }, { unique: true });

db.suppliers.createIndex({ "name": 1 }, { unique: true });
db.suppliers.createIndex({ "document_number": 1 }, { sparse: true });

// Insert initial data for testing
db.categories.insertMany([
  { 
    name: "Eletrônicos", 
    slug: "eletronicos", 
    description: "Produtos eletrônicos em geral",
    is_active: true,
    created_at: new Date(),
    updated_at: new Date()
  },
  { 
    name: "Vestuário", 
    slug: "vestuario", 
    description: "Roupas e acessórios",
    is_active: true,
    created_at: new Date(),
    updated_at: new Date()
  },
  { 
    name: "Calçados", 
    slug: "calcados", 
    description: "Todos os tipos de calçados",
    is_active: true,
    created_at: new Date(),
    updated_at: new Date()
  }
]);

db.suppliers.insertOne({
  name: "Fornecedor Default",
  document_number: "12345678900123",
  email: "contato@fornecedor.com",
  phone: "(11) 99999-9999",
  is_active: true,
  created_at: new Date(),
  updated_at: new Date()
});

print('MongoDB initialized successfully for ERP API');
