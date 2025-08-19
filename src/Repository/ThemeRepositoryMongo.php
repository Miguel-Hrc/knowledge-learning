<?php

namespace App\Repository;

use App\Document\ThemeDocument;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository class for managing ThemeDocument documents in MongoDB.
 *
 * This class provides methods to interact with the MongoDB database for ThemeDocument documents,
 * including finding, adding, and removing themes.
 *
 * @extends DocumentRepository<ThemeDocument>
 */
class ThemeRepositoryMongo extends DocumentRepository
{

}