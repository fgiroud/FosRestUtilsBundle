<?php

namespace Fgir\FosRestUtilsBundle\CrudHelper;

use Doctrine\ORM\EntityManager;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdater;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DoctrineOrmHelper
{
    private $om;
    private $formFactory;
    private $queryBuilderUpdater;
    private $paginator;

    public function __construct(EntityManager $om, FormFactory $formFactory, FilterBuilderUpdater $queryBuilderUpdater, PaginatorInterface $paginator)
    {
        $this->om = $om;
        $this->formFactory = $formFactory;
        $this->queryBuilderUpdater = $queryBuilderUpdater;
        $this->paginator = $paginator;
    }

    /**
     * Return an array containing api results for a GET_MANY operation
     * @param  string $model your model name
     * @param  string $formType
     * @param  array $filterValues
     * @return array
     */
    public function getMultipleDocuments($model, $formType, $filterValues)
    {
        $form = $this->formFactory->createNamed('', $formType);
        $form->submit($filterValues);

        if (!$form->isValid()) {
            return [
                'errors' => Utils::getFormErrors($form),
                'code' => 400,
            ];
        } else {
            $qb = $this->createQueryBuilder($model);
            $this->queryBuilderUpdater->addFilterConditions($form, $qb);

            $sorting = 'id';
            $direction = 'DESC';

            if (is_array($filterValues) && array_key_exists('sort', 'filterValues')) {
                $querySortingParameter = $filterValues['sort'];
                $direction = substr($querySortingParameter, 0, 1) == '-' ? 'DESC' : 'ASC';
                $sorting = substr($querySortingParameter, 0, 1) == '-' ? substr($querySortingParameter, 1) : $querySortingParameter;
            }
            $qb->orderBy($sorting, $direction);

            $limit = isset($filterValues['limit']) ? $filterValues['limit'] : 10;
            $page = isset($filterValues['page']) ? $filterValues['page'] : 1;

            $pagination = $this->paginator->paginate(
                $qb,
                $page,
                $limit
            );

            $documents = [];
            foreach ($pagination as $document) {
                $documents[] = $document;
            }

            return [
                'data' => $documents,
                'total_count' => $pagination->getTotalItemCount(),
                'pages' => ceil($pagination->getTotalItemCount() / $limit),
                'code' => 200,
            ];
        }

    }

    public function getSingleDocument($model, $id)
    {
        $document = $this->find($model, $id);
        return [
            'data' => $document,
            'code' => 200,
        ];
    }

    public function createDocument($model, $values, $formType)
    {
        $className = $this->om->getRepository($model)->getClassName();
        $newDocument = new $className();
        $form = $this->formFactory->createNamed('', $formType, $newDocument);

        $form->submit($values);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();
            $this->om->persist($document);
            $this->om->flush();
            return [
                'code' => 200,
                'data' => $document,
            ];

        } else {
            return [
                'errors' => Utils::getFormErrors($form),
                'code' => 400,
            ];
        }
    }

    public function partiallyUpdateDocument($model, $id, $values, $formType)
    {
        $document = $this->find($model, $id);
        $form = $this->formFactory->createNamed('', $formType, $document);
        $form->submit($values, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->getData();
            $this->om->persist($document);
            $this->om->flush();
            return [
                'code' => 200,
                'data' => $document,
            ];

        } else {
            return [
                'errors' => Utils::getFormErrors($form),
                'code' => 400,
            ];
        }
    }

    public function deleteDocument($model, $id)
    {
        $document = $this->find($model, $id);
        $this->om->remove($document);
        $this->om->flush();

        return [
            'code' => 200,
            'data' => null,
        ];
    }

    public function find($model, $id)
    {
        $qb = $this->createQueryBuilder($model);
        $qb->where('e.id = :id')->setParameter('id', $id);
        $documents = $qb->getQuery()->getResult();
        $document = count($documents) ? $documents[0] : null;

        if (!$document) {
            throw new NotFoundHttpException('Document not found');
        }
        return $document;
    }

    protected function createQueryBuilder($model)
    {
        $qb = $this->om->getRepository($model)->createQueryBuilder('e');
        return $qb;
    }

}
