<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\BaseInfo;
use Eccube\Form\DataTransformer;
use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\KanaType;
use Eccube\Form\Type\NameType;
use Eccube\Form\Type\PriceType;
use Eccube\Form\Type\TelType;
use Eccube\Form\Type\ZipType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OrderType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * OrderType constructor.
     * @param EntityManagerInterface $entityManager
     * @param EccubeConfig $eccubeConfig
     * @param BaseInfo $BaseInfo
     */
    public function __construct(EntityManagerInterface $entityManager, EccubeConfig $eccubeConfig, BaseInfo $BaseInfo)
    {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
        $this->BaseInfo = $BaseInfo;
    }


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', NameType::class, array(
                'required' => false,
                'options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ),
            ))
            ->add('kana', KanaType::class, array(
                'required' => false,
                'options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ),
            ))
            ->add('company_name', TextType::class, array(
                'label' => 'order.label.company_name',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    )),
                ),
            ))
            ->add('zip', ZipType::class, array(
                'required' => false,
                'options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                    'attr' => array('class' => 'p-postal-code'),
                ),
            ))
            ->add('address', AddressType::class, array(
                'required' => false,
                'pref_options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                    'attr' => array('class' => 'p-region-id'),
                ),
                'addr01_options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array(
                            'max' => $this->eccubeConfig['eccube_mtext_len'],
                        )),
                    ),
                    'attr' => array('class' => 'p-locality p-street-address'),
                ),
                'addr02_options' => array(
                    'required' => false,
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array(
                            'max' => $this->eccubeConfig['eccube_mtext_len'],
                        )),
                    ),
                    'attr' => array('class' => 'p-extended-address'),
                ),
            ))
            ->add('email', EmailType::class, array(
                'required' => false,
                'label' => 'order.label.email',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Email(array('strict' => true)),
                ),
            ))
            ->add('tel', TelType::class, array(
                'required' => false,
                'options' => array(
                    'constraints' => array(
                        new Assert\NotBlank(),
                    ),
                ),
            ))
            ->add('fax', TelType::class, array(
                'label' => 'order.label.fax_number',
                'required' => false,
            ))
            ->add('company_name', TextType::class, array(
                'label' => 'order.label.company_name',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->eccubeConfig['eccube_stext_len'],
                    )),
                ),
            ))
            ->add('message', TextareaType::class, array(
                'label' => 'order.label.inquiry',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->eccubeConfig['eccube_ltext_len'],
                    )),
                ),
            ))
            ->add('discount', PriceType::class, array(
                'required' => false,
                'label' => 'order.label.discount',
            ))
            ->add('delivery_fee_total', PriceType::class, array(
                'required' => false,
                'label' => 'order.label.shipping_charge',
            ))
            ->add('charge', PriceType::class, array(
                'required' => false,
                'label' => 'order.label.commision',
            ))
            ->add('add_point', NumberType::class, array(
                'required' => false,
                'label' => '加算ポイント', // TODO 未翻訳
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid',
                    )),
                ),
                'attr' => array('readonly' => true),
            ))
            ->add('use_point', NumberType::class, array(
                'required' => false,
                'label' => '利用ポイント', // TODO 未翻訳
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d+$/u",
                        'message' => 'form.type.numeric.invalid',
                    )),
                ),
            ))
            ->add('note', TextareaType::class, array(
                'label' => 'order.label.owner_note',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->eccubeConfig['eccube_ltext_len'],
                    )),
                ),
            ))
            ->add('OrderStatus', EntityType::class, array(
                'class' => 'Eccube\Entity\Master\OrderStatus',
                'choice_label' => 'name',
                'placeholder' => 'order.placeholder.select',
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.sort_no', 'ASC');
                },
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('Payment', EntityType::class, array(
                'required' => false,
                'class' => 'Eccube\Entity\Payment',
                'choice_label' => 'method',
                'placeholder' => 'order.placeholder.select',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('OrderItems', CollectionType::class, array(
                'entry_type' => OrderItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'data' => $options['SortedItems'],
            ))
            ->add('OrderItemsErrors', TextType::class, [
                'mapped' => false,
            ]);

        $builder
            ->add($builder->create('Customer', HiddenType::class)
                ->addModelTransformer(new DataTransformer\EntityToIdTransformer(
                    $this->entityManager,
                    '\Eccube\Entity\Customer'
                )));

        // 選択された支払い方法の名称をエンティティにコピーする
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $Order = $event->getData();
            $Payment = $Order->getPayment();
            if (!is_null($Payment)) {
                $Order->setPaymentMethod($Payment->getMethod());
            }
        });
        // 会員受注の場合、会員の性別/職業/誕生日をエンティティにコピーする
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $Order = $event->getData();
            $Customer = $Order->getCustomer();
            if (!is_null($Customer)) {
                $Order->setSex($Customer->getSex());
                $Order->setJob($Customer->getJob());
                $Order->setBirth($Customer->getBirth());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Order',
            'orign_order' => null,
            'SortedItems' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'order';
    }
}
