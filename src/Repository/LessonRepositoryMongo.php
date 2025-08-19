<?php

namespace App\Repository;

use App\Document\LessonDocument;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * Repository class for managing LessonDocument documents in MongoDB.
 *
 * This class provides methods to access and manipulate LessonDocument
 * entities using Doctrine MongoDB ODM. You can add custom query methods
 * specific to LessonDocument here.
 */
class LessonRepositoryMongo extends DocumentRepository
{
}