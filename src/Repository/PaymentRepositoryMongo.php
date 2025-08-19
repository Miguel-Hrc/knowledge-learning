<?php

namespace App\Repository;

use App\Document\PaymentDocument;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository class for managing PaymentDocument entities in MongoDB.
 *
 * This class extends DocumentRepository and provides
 * convenient methods to query, find, and manage PaymentDocument entities.
 * Custom query methods specific to PaymentDocument can be added here.
 */
class PaymentRepositoryMongo extends DocumentRepository
{
}