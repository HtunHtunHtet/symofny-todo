<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NoteController
 * @package App\Controller
 */
class NoteController extends AbstractFOSRestController
{
	
	/**
	 * @var NoteRepository
	 */
	private $noteRepository;
	
	/**
	 * @var EntityManager
	 */
	private $entityManager;
	
	public function __construct(EntityManagerInterface $entityManager, NoteRepository $noteRepository)
	{
		$this->noteRepository = $noteRepository;
		$this->entityManager  = $entityManager;
	}
	
	public function getNoteAction(Note $note){
		return $this->view($note , Response::HTTP_OK);
	}
	
	public function deleteNoteAction(Note $note)
	{
		if ($note){
			$this->entityManager->remove($note);
			$this->entityManager->flush();
			
			return $this->view(null, Response::HTTP_NO_CONTENT);
		}
		
		return $this->view(['message' => 'something went wrong'], Response::HTTP_INTERNAL_SERVER_ERROR);
	}
}
