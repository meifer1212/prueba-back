# Documentacion API

## 1 Listar bodegas
GET para obtener todas las bodegas ordenadas alfabeticamente por nombre.

```bash
curl --location 'http://localhost/api/bodegas'
```

## 2 Crear bodega
POST para registrar una nueva bodega.

```bash
curl --location 'http://localhost/api/bodegas' \
--header 'Content-Type: application/json' \
--data-raw '{
  "nombre": "Bodega QA",
  "id_responsable": 1,
  "estado": true,
  "created_by": 1,
  "updated_by": 1
}'
```

## 3 Listar productos por total
GET para listar productos ordenados de mayor a menor por el total de inventario (campo Total).

```bash
curl --location 'http://localhost/api/productos/total-desc'
```

## 4 Crear producto con inventario inicial
POST para crear un producto y asignarle cantidad inicial en la bodega por default.

```bash
curl --location 'http://localhost/api/productos' \
--header 'Content-Type: application/json' \
--data-raw '{
  "nombre": "Producto API Test",
  "descripcion": "Creado por curl",
  "estado": true,
  "cantidad_inicial": 120,
  "created_by": 1,
  "updated_by": 1
}'
```

## 5 Insertar o actualizar inventario
POST para crear inventario si la combinacion producto+bodega no existe, o sumar cantidad si ya existe.

```bash
curl --location 'http://localhost/api/inventarios' \
--header 'Content-Type: application/json' \
--data-raw '{
  "id_producto": 1,
  "id_bodega": 1,
  "cantidad": 35,
  "created_by": 1,
  "updated_by": 1
}'
```

## 6 Trasladar inventario entre bodegas
POST para mover unidades de una bodega origen a una bodega destino, validando disponibilidad en origen.

```bash
curl --location 'http://localhost/api/inventarios/trasladar' \
--header 'Content-Type: application/json' \
--data-raw '{
  "id_producto": 1,
  "id_bodega_origen": 1,
  "id_bodega_destino": 2,
  "cantidad": 5,
  "created_by": 1,
  "updated_by": 1
}'
```