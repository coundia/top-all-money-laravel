<?php

namespace App\OpenApi;

/**
 * =========================
 * ACCOUNT
 * =========================
 * @OA\Schema(
 *   schema="Account",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", nullable=true, example="ACC-001"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="currency", type="string", nullable=true, example="XOF"),
 *   @OA\Property(property="status", type="string", nullable=true),
 *   @OA\Property(property="typeAccount", type="string", nullable=true),
 *   @OA\Property(property="dateStartAccount", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="dateEndAccount", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="balance", type="integer", example=0),
 *   @OA\Property(property="balance_prev", type="integer", example=0),
 *   @OA\Property(property="balance_blocked", type="integer", example=0),
 *   @OA\Property(property="balance_init", type="integer", example=0),
 *   @OA\Property(property="balance_goal", type="integer", example=0),
 *   @OA\Property(property="balance_limit", type="integer", example=0),
 *   @OA\Property(property="isDefault", type="boolean", example=false),
 *   @OA\Property(property="isShared", type="boolean", example=false),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="AccountCreateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", example="ACC-001"),
 *   @OA\Property(property="currency", type="string", example="XOF"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="isDefault", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *   schema="AccountUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string", example="ACC-002"),
 *   @OA\Property(property="currency", type="string", example="XOF"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="isDefault", type="boolean", example=true),
 *   @OA\Property(property="status", type="string", example="ACTIVE")
 * )
 *
 * =========================
 * CATEGORY
 * =========================
 * (On conserve uniquement le schéma de ressource "Category" ici
 *  pour éviter les collisions avec les schémas Request définis
 *  dans les classes CategoryStoreRequest / CategoryUpdateRequest.)
 *
 * @OA\Schema(
 *   schema="Category",
 *   type="object",
 *   required={"id","typeEntry"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", example="CAT-001"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}, example="DEBIT"),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="isShared", type="boolean", example=false),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true)
 * )
 *
 * =========================
 * TRANSACTION ENTRY
 * =========================
 * @OA\Schema(
 *   schema="TransactionEntry",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="amount", type="integer", example=0),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}, example="DEBIT"),
 *   @OA\Property(property="dateTransaction", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true),
 *   @OA\Property(property="entityName", type="string", nullable=true),
 *   @OA\Property(property="entityId", type="string", nullable=true),
 *   @OA\Property(property="accountId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="categoryId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="customerId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="debtId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="isDirty", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *   schema="TransactionEntryCreateRequest",
 *   type="object",
 *   required={"amount","typeEntry"},
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="amount", type="integer", example=1000),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}),
 *   @OA\Property(property="dateTransaction", type="string", format="date-time"),
 *   @OA\Property(property="accountId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="categoryId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="customerId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="debtId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="TransactionEntryUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="amount", type="integer"),
 *   @OA\Property(property="typeEntry", type="string", enum={"DEBIT","CREDIT"}),
 *   @OA\Property(property="dateTransaction", type="string", format="date-time"),
 *   @OA\Property(property="accountId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="categoryId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="customerId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="debtId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true)
 * )
 *
 * =========================
 * PRODUCT
 * =========================
 * @OA\Schema(
 *   schema="Product",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="barcode", type="string", nullable=true),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="categoryId", type="string", nullable=true),
 *   @OA\Property(property="defaultPrice", type="integer", example=0),
 *   @OA\Property(property="statuses", type="string", nullable=true),
 *   @OA\Property(property="purchasePrice", type="integer", example=0),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *   schema="ProductCreateRequest",
 *   type="object",
 *   required={"code","name"},
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="barcode", type="string", nullable=true),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="categoryId", type="string", nullable=true),
 *   @OA\Property(property="defaultPrice", type="integer", example=0),
 *   @OA\Property(property="purchasePrice", type="integer", example=0)
 * )
 *
 * @OA\Schema(
 *   schema="ProductUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="barcode", type="string", nullable=true),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="categoryId", type="string", nullable=true),
 *   @OA\Property(property="defaultPrice", type="integer"),
 *   @OA\Property(property="purchasePrice", type="integer")
 * )
 *
 * =========================
 * TRANSACTION ITEM
 * =========================
 * @OA\Schema(
 *   schema="TransactionItem",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="transactionId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="productId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="label", type="string", nullable=true),
 *   @OA\Property(property="quantity", type="integer", example=1),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="unitPrice", type="integer", example=0),
 *   @OA\Property(property="total", type="integer", example=0),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *   schema="TransactionItemCreateRequest",
 *   type="object",
 *   required={"label","quantity"},
 *   @OA\Property(property="transactionId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="productId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="label", type="string"),
 *   @OA\Property(property="quantity", type="integer", example=1),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="unitPrice", type="integer", example=0),
 *   @OA\Property(property="total", type="integer", example=0),
 *   @OA\Property(property="notes", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="TransactionItemUpdateRequest",
 *   type="object",
 *   @OA\Property(property="transactionId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="productId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="label", type="string"),
 *   @OA\Property(property="quantity", type="integer"),
 *   @OA\Property(property="unitId", type="string", nullable=true),
 *   @OA\Property(property="unitPrice", type="integer"),
 *   @OA\Property(property="total", type="integer"),
 *   @OA\Property(property="notes", type="string", nullable=true)
 * )
 *
 * =========================
 * COMPANY
 * =========================
 * @OA\Schema(
 *   schema="Company",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true),
 *   @OA\Property(property="website", type="string", nullable=true),
 *   @OA\Property(property="taxId", type="string", nullable=true),
 *   @OA\Property(property="currency", type="string", nullable=true),
 *   @OA\Property(property="addressLine1", type="string", nullable=true),
 *   @OA\Property(property="addressLine2", type="string", nullable=true),
 *   @OA\Property(property="city", type="string", nullable=true),
 *   @OA\Property(property="region", type="string", nullable=true),
 *   @OA\Property(property="country", type="string", nullable=true),
 *   @OA\Property(property="postalCode", type="string", nullable=true),
 *   @OA\Property(property="isDefault", type="boolean", example=false),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *   schema="CompanyCreateRequest",
 *   type="object",
 *   required={"code","name"},
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="CompanyUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true)
 * )
 *
 * =========================
 * CUSTOMER
 * =========================
 * @OA\Schema(
 *   schema="Customer",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="firstName", type="string", nullable=true),
 *   @OA\Property(property="lastName", type="string", nullable=true),
 *   @OA\Property(property="fullName", type="string", nullable=true),
 *   @OA\Property(property="balance", type="integer", example=0),
 *   @OA\Property(property="balanceDebt", type="integer", example=0),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true),
 *   @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="addressLine1", type="string", nullable=true),
 *   @OA\Property(property="addressLine2", type="string", nullable=true),
 *   @OA\Property(property="city", type="string", nullable=true),
 *   @OA\Property(property="region", type="string", nullable=true),
 *   @OA\Property(property="country", type="string", nullable=true),
 *   @OA\Property(property="postalCode", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="account", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="CustomerCreateRequest",
 *   type="object",
 *   required={"code"},
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="fullName", type="string", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="CustomerUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="fullName", type="string", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", nullable=true)
 * )
 *
 * =========================
 * STOCK LEVEL
 * =========================
 * @OA\Schema(
 *   schema="StockLevel",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="stockOnHand", type="integer", example=0),
 *   @OA\Property(property="stockAllocated", type="integer", example=0),
 *   @OA\Property(property="productVariantId", type="string", format="uuid"),
 *   @OA\Property(property="companyId", type="string", format="uuid"),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="StockLevelCreateRequest",
 *   type="object",
 *   required={"productVariantId","companyId"},
 *   @OA\Property(property="productVariantId", type="string", format="uuid"),
 *   @OA\Property(property="companyId", type="string", format="uuid"),
 *   @OA\Property(property="stockOnHand", type="integer", example=0),
 *   @OA\Property(property="stockAllocated", type="integer", example=0)
 * )
 *
 * @OA\Schema(
 *   schema="StockLevelUpdateRequest",
 *   type="object",
 *   @OA\Property(property="stockOnHand", type="integer", nullable=true),
 *   @OA\Property(property="stockAllocated", type="integer", nullable=true)
 * )
 *
 * =========================
 * STOCK MOVEMENT
 * =========================
 * @OA\Schema(
 *   schema="StockMovement",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="type_stock_movement", type="string"),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="quantity", type="integer", example=0),
 *   @OA\Property(property="companyId", type="string", format="uuid"),
 *   @OA\Property(property="productVariantId", type="string", format="uuid"),
 *   @OA\Property(property="orderLineId", type="string", nullable=true),
 *   @OA\Property(property="discriminator", type="string", nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="StockMovementCreateRequest",
 *   type="object",
 *   required={"type_stock_movement","quantity","companyId","productVariantId"},
 *   @OA\Property(property="type_stock_movement", type="string"),
 *   @OA\Property(property="quantity", type="integer", example=1),
 *   @OA\Property(property="companyId", type="string", format="uuid"),
 *   @OA\Property(property="productVariantId", type="string", format="uuid")
 * )
 *
 * @OA\Schema(
 *   schema="StockMovementUpdateRequest",
 *   type="object",
 *   @OA\Property(property="type_stock_movement", type="string"),
 *   @OA\Property(property="quantity", type="integer", nullable=true),
 *   @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="productVariantId", type="string", format="uuid", nullable=true)
 * )
 *
 * =========================
 * DEBT
 * =========================
 * @OA\Schema(
 *   schema="Debt",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true),
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="balance", type="integer", example=0),
 *   @OA\Property(property="balanceDebt", type="integer", example=0),
 *   @OA\Property(property="dueDate", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="statuses", type="string", nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="customerId", type="string", format="uuid", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *   schema="DebtCreateRequest",
 *   type="object",
 *   required={"code"},
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="balance", type="integer", example=0),
 *   @OA\Property(property="balanceDebt", type="integer", example=0)
 * )
 *
 * @OA\Schema(
 *   schema="DebtUpdateRequest",
 *   type="object",
 *   @OA\Property(property="code", type="string"),
 *   @OA\Property(property="notes", type="string", nullable=true),
 *   @OA\Property(property="balance", type="integer", nullable=true),
 *   @OA\Property(property="balanceDebt", type="integer", nullable=true),
 *   @OA\Property(property="dueDate", type="string", format="date-time", nullable=true)
 * )
 *
 * =========================
 * ACCOUNT USER
 * =========================
 * @OA\Schema(
 *   schema="AccountUser",
 *   type="object",
 *   required={"id"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="code", type="string", nullable=true),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="user", type="string", nullable=true),
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="identify", type="string", nullable=true),
 *   @OA\Property(property="role", type="string", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true),
 *   @OA\Property(property="invitedBy", type="string", nullable=true),
 *   @OA\Property(property="invitedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="acceptedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="revokedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="createdAt", type="string", format="date-time"),
 *   @OA\Property(property="updatedAt", type="string", format="date-time"),
 *   @OA\Property(property="deletedAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="syncAt", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="version", type="integer", example=0),
 *   @OA\Property(property="isDirty", type="boolean", example=true),
 *   @OA\Property(property="remoteId", type="string", nullable=true),
 *   @OA\Property(property="createdBy", type="string", nullable=true),
 *   @OA\Property(property="localId", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="AccountUserCreateRequest",
 *   type="object",
 *   required={"email"},
 *   @OA\Property(property="email", type="string", format="email"),
 *   @OA\Property(property="account", type="string", nullable=true),
 *   @OA\Property(property="user", type="string", nullable=true),
 *   @OA\Property(property="role", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="AccountUserUpdateRequest",
 *   type="object",
 *   @OA\Property(property="email", type="string", format="email", nullable=true),
 *   @OA\Property(property="phone", type="string", nullable=true),
 *   @OA\Property(property="role", type="string", nullable=true),
 *   @OA\Property(property="status", type="string", nullable=true)
 * )
 *
 * =========================
 * CONVERSATION
 * =========================
 * @OA\Schema(
 *   schema="Conversation",
 *   type="object",
 *   required={"id","title"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="ConversationCreateRequest",
 *   type="object",
 *   required={"title"},
 *   @OA\Property(property="title", type="string")
 * )
 *
 * @OA\Schema(
 *   schema="ConversationUpdateRequest",
 *   type="object",
 *   @OA\Property(property="title", type="string")
 * )
 *
 * =========================
 * MESSAGE
 * =========================
 * @OA\Schema(
 *   schema="Message",
 *   type="object",
 *   required={"id","conversation_id","body"},
 *   @OA\Property(property="id", type="string", format="uuid"),
 *   @OA\Property(property="conversation_id", type="string", format="uuid"),
 *   @OA\Property(property="body", type="string"),
 *   @OA\Property(property="sender", type="string", nullable=true),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *   schema="MessageCreateRequest",
 *   type="object",
 *   required={"conversation_id","body"},
 *   @OA\Property(property="conversation_id", type="string", format="uuid"),
 *   @OA\Property(property="body", type="string"),
 *   @OA\Property(property="sender", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *   schema="MessageUpdateRequest",
 *   type="object",
 *   @OA\Property(property="body", type="string"),
 *   @OA\Property(property="sender", type="string", nullable=true)
 * )
 *
 */
final class Schemas {}
