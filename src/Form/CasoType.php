<?php

namespace App\Form;

use App\Entity\Caso;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;

class CasoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add ('asunto', TextType::class,array(
                'attr' => array(
                    'id' => '_asunto',
                    'name' => '_asunto'
                )
            ))
            ->add ('correo', TextType::class,array(
                'attr' => array(
                    'id' => '_correo',
                    'name' => '_correo',
                    'required' => 'true'
                )
            ))
            ->add ('contacto', TextType::class,array(
                'attr' => array(
                    'id' => '_contacto',
                    'name' => '_contacto',
                    'required' => 'true'
                )
            ))
            ->add ('telefono', IntegerType::class,array(
                'attr' => array(
                    'id' => '_telefono',
                    'name' => '_telefono',
                    'required' => 'true'
                )
            ))
            ->add ('extension', IntegerType::class,array(
                'attr' => array(
                    'id' => '_extension',
                    'name' => '_extension',
                    'class' => 'form-control'
                )
            ))
            ->add ('descripcion', TextareaType::class,array(
                'attr' => array(
                    'id' => '_descripcion',
                    'name' => '_descripcion',
                    'class' => 'form-control'
                )
            ))
            ->add('clienteRel', EntityType::class, array(
                'class' => 'AppBundle:Cliente',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nombreComercial', 'ASC');},
                'choice_label' => 'nombreComercial',
                'required' => true))
            ->add('prioridadRel', EntityType::class, array(
                'class' => 'AppBundle:Prioridad',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nombre', 'ASC');},
                'choice_label' => 'nombre',
                'required' => true))
            ->add('categoriaRel', EntityType::class, array(
                'class' => 'AppBundle:CasoCategoria',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.nombre', 'ASC');},
                'choice_label' => 'nombre',
                'required' => true))
//            Botón Guardar
            ->add ('btnGuardar', SubmitType::class, array(
                'attr' => array(
                    'id' => '_btnGuardar',
                    'name' => '_btnGuardar'
                ), 'label' => 'GUARDAR'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            //'data_class' => Caso::class,
        ]);
    }
}
