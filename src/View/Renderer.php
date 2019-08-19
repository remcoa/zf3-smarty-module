<?php
namespace Smarty\View;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Model\ModelInterface;
use Zend\View\Exception\DomainException;
use Smarty;
use ArrayObject;

class Renderer implements RendererInterface
{
    /**
     * @var Smarty
     */
    protected $engine;
    /**
     * @var ResolverInterface
     */
    protected $resolver;
    /**
     * Template suffix for this renderer
     * @var string
     */
    protected $suffix;

    public function setEngine(Smarty $engine)
    {
        $this->engine = $engine;
        $this->engine->assign('this', $this);
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render($nameOrModel, $values = null)
    {
        if ($nameOrModel instanceof ModelInterface) {
            $model = $nameOrModel;
            $nameOrModel = $nameOrModel->getTemplate();
            if (empty($nameOrModel)) {
                throw new DomainException(sprintf(
                    '%s: recieved View Model argument, but template is empty.',
                    __METHOD__
                ));
            }
            $values = $model->getVariables();
            unset($model);
        }
        if (!($file = $this->resolver->resolve($nameOrModel))) {
            throw new DomainException(sprintf(
                'Unable to find template "%s"',
                $nameOrModel
            ));
        }

        if ($values instanceof ArrayObject) {
            $values = $values->getArrayCopy();
        }

        $smarty = $this->getEngine();
        $smarty->clearAllAssign();
        $this->engine->assign('this', $this);
        $smarty->assign($values);

        $content = $smarty->fetch($file);

        return $content;
    }

    public function canRender($nameOrModel)
    {
        if ($nameOrModel instanceof ModelInterface) {
            $nameOrModel = $nameOrModel->getTemplate();
        }
        $tpl = $this->resolver->resolve($nameOrModel);
        $ext = pathinfo($tpl, PATHINFO_EXTENSION);
        if ($tpl && $ext == $this->getSuffix()) {
            return true;
        }
        return false;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }
}
