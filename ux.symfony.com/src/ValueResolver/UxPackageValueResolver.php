<?php
namespace App\ValueResolver;

use App\Model\UxPackage;
use App\Service\UxPackageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UxPackageValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly UxPackageRepository $packageRepository,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UxPackage::class  !== $argument->getType()) {
            return [];
        }

        $slug = $request->attributes->get('ux_package');
        if (!is_string($slug)) {
            return [];
        }

        return [$this->packageRepository->find($slug)];
    }
}
