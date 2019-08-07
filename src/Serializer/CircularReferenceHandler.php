<?php

	namespace App\Serializer;
	
	use App\Entity\Note;
	use App\Entity\Task;
	use App\Entity\TaskList;
	use Symfony\Component\Routing\RouterInterface;
	
	class CircularReferenceHandler
	{
		
		private $router;
		
		public function __construct(RouterInterface $router)
		{
			$this->router = $router;
		}
		
		public function __invoke($object) {
			// TODO: Implement __invoke() method.
			
			switch ($object){
				case $object instanceOf TaskList:
					return $this->router->generate('get_list', ['list' => $object->getId() ] );
				case $object instanceOf Task:
					return $this->router->generate('get_task',['task' => $object->getId()]);
				case $object instanceOf Note:
					return $this->router->generate('get_note', ['note' => $object->getId()]);
				
			}
			
			return $object->getId();
			
		}
	}