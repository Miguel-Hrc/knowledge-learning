<?php

namespace App\Repository;

use App\Document\CommandDocument;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository class for managing CommandDocument documents in MongoDB.
 *
 * This class provides methods to access and manipulate CommandDocument
 * objects from the MongoDB database using Doctrine ODM.
 */
class CommandRepositoryMongo extends DocumentRepository
{
}