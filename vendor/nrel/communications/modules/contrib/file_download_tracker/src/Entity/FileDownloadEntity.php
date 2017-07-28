<?php

namespace Drupal\file_download_tracker\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the File download entity entity.
 *
 * @ingroup file_download_tracker
 *
 * @ContentEntityType(
 *   id = "file_download_entity",
 *   label = @Translation("File download entity"),
 *   handlers = {
 *     "storage" = "Drupal\file_download_tracker\FileDownloadEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\file_download_tracker\FileDownloadEntityListBuilder",
 *     "views_data" = "Drupal\file_download_tracker\Entity\FileDownloadEntityViewsData",
 *     "translation" = "Drupal\file_download_tracker\FileDownloadEntityTranslationHandler",
 *   },
 *   file_download_permission = "access all reports",
 *   base_table = "file_download_entity",
 *   data_table = "file_download_entity_field_data",
 *   revision_table = "file_download_entity_revision",
 *   revision_data_table = "file_download_entity_field_revision",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "entity_type" = "entity_type",
 *     "entity_id" = "entity_id",
 *     "ip_address" = "ip_address",
 *   },
 * )
 */
class FileDownloadEntity extends RevisionableContentEntityBase implements FileDownloadEntityInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
        $values += array(
            'user_id' => \Drupal::currentUser()->id(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preSave(EntityStorageInterface $storage)
    {
        parent::preSave($storage);

        foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
            $translation = $this->getTranslation($langcode);

            // If no owner has been set explicitly, make the anonymous user the owner.
            if (!$translation->getOwner()) {
                $translation->setOwnerId(0);
            }
        }

        // If no revision author has been set explicitly, make the file_download_entity owner the
        // revision author.
        if (!$this->getRevisionUser()) {
            $this->setRevisionUserId($this->getOwnerId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('name')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->set('name', $name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp)
    {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setPublished($published)
    {
        $this->set('status', $published ? TRUE : FALSE);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRevisionCreationTime()
    {
        return $this->get('revision_timestamp')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setRevisionCreationTime($timestamp)
    {
        $this->set('revision_timestamp', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRevisionUser()
    {
        return $this->get('revision_uid')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function setRevisionUserId($uid)
    {
        $this->set('revision_uid', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);
        // Add fields.
        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Authored by'))
            ->setDescription(t('The user ID of author of the file download tracker entity.'))
            ->setRevisionable(TRUE)
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', array(
                'label' => 'hidden',
                'type' => 'author',
                'weight' => 0,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'entity_reference_autocomplete',
                'weight' => 5,
                'settings' => array(
                    'match_operator' => 'CONTAINS',
                    'size' => '60',
                    'autocomplete_type' => 'tags',
                    'placeholder' => '',
                ),
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the file download tracker entity.'))
            ->setRevisionable(TRUE)
            ->setSettings(array(
                'max_length' => 50,
                'text_processing' => 0,
            ))
            ->setDefaultValue('')
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -4,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textfield',
                'weight' => -4,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['entity_type'] = BaseFieldDefinition::create('list_string')
            ->setLabel(t('Type'))
            ->setDescription(t('The type of file download tracker entity.'))
            ->setSettings(array(
                'allowed_values' => array(
                    'file' => 'file',
                    'page' => 'page',
                ),
            ))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -4,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'options_select',
                'weight' => -4,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['entity_id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('Entity ID'))
            ->setDescription(t('Entity ID'))
            ->setSetting('unsigned', TRUE)
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'integer',
                'weight' => -2,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'integer',
                'weight' => -2,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['ip_address'] = BaseFieldDefinition::create('string')
            ->setLabel(t('IP Address'))
            ->setDescription(t('The IP address of the page is user accessing'))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string',
                'weight' => -1,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'string',
                'weight' => -1,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);


        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Publishing status'))
            ->setDescription(t('A boolean indicating whether the file download tracker entity is published.'))
            ->setRevisionable(TRUE)
            ->setDefaultValue(TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Revision timestamp'))
            ->setDescription(t('The time that the current revision was created.'))
            ->setQueryable(FALSE)
            ->setRevisionable(TRUE);

        $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Revision user ID'))
            ->setDescription(t('The user ID of the author of the current revision.'))
            ->setSetting('target_type', 'user')
            ->setQueryable(FALSE)
            ->setRevisionable(TRUE);

        $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Revision translation affected'))
            ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
            ->setReadOnly(TRUE)
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE);

        return $fields;

    }
}
