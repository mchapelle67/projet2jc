<?php

namespace App\Controller\SuperAdmin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PasswordField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword(
                $this->userPasswordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword())
        );
            $entityInstance->eraseCredentials();
        }
            parent::persistEntity($entityManager, $entityInstance);
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('email')
                ->setFormTypeOption('validation_groups', ['Default', 'create']),
            TextField::new('plainPassword', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->onlyWhenCreating()
                ->setFormTypeOption('validation_groups', ['Default', 'create'])
                ->setFormTypeOption('required', true),
            ArrayField::new('roles'),
        ];
    }
    
}
