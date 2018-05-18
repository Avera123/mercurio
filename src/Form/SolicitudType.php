<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

class SolicitudType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $arrSolicitudesTipo = $options['data']['solicitudesTipo'];
        $arSolicitud = $options['data']['arSolicitud'];

        $solicitud = $arSolicitud[0];

        $builder
            ->add('descripcion', TextareaType::class, array(
                'data' => isset($solicitud) ? $solicitud['descripcion'] : '',
                'attr' => array(
                    'id' => '_descripcion',
                    'name' => '_descripcion',
                    'class' => 'form-control'
                )
            ))
            ->add('nombre', TextType::class, array(
                'data' => isset($solicitud) ? $solicitud['nombre'] : '',
                'attr' => array(
                    'id' => '_nombre',
                    'name' => '_nombre',
                    'class' => 'form-control'
                )
            ))
            ->add('solicitudTipoRel', ChoiceType::class, array(
                'choices' => $arrSolicitudesTipo,
                'choice_label' => 'nombre',
                'choice_value' => 'codigoSolicitudTipoPk',
                'placeholder' => 'Seleccione una opción',
                'label' => 'Tipo solicitud: ',
                'attr' => array(
                    'class' => 'form-control',
                    'data-value' => isset($solicitud['codigoSolicitudTipoPk']) ? $solicitud['codigoSolicitudTipoPk'] : '',
                )
            ))
//            Botón Guardar
            ->add('btnGuardar', SubmitType::class, array(
                'attr' => array(
                    'id' => '_btnGuardar',
                    'name' => '_btnGuardar'
                ), 'label' => 'GUARDAR'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // uncomment if you want to bind to a class
            //'data_class' => Caso::class,
        ]);
    }
}
