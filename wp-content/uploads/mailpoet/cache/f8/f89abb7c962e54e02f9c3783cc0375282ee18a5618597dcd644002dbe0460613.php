<?php

use MailPoetVendor\Twig\Environment;
use MailPoetVendor\Twig\Error\LoaderError;
use MailPoetVendor\Twig\Error\RuntimeError;
use MailPoetVendor\Twig\Extension\SandboxExtension;
use MailPoetVendor\Twig\Markup;
use MailPoetVendor\Twig\Sandbox\SecurityError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedTagError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFilterError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFunctionError;
use MailPoetVendor\Twig\Source;
use MailPoetVendor\Twig\Template;

/* newsletter/templates/blocks/text/settings.hbs */
class __TwigTemplate_b4dbf9a523d8b0e8922153ffa25438bc640ecdd460be0cb1dab94e3098c0864e extends \MailPoetVendor\Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<h3>";
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Text");
        echo "</h3>
";
        // line 2
        echo $this->extensions['MailPoet\Twig\I18n']->translate("Text");
        echo ": <textarea name=\"text\" class=\"text\" rows=\"5\" cols=\"40\">{{ model.text }}</textarea>
";
    }

    public function getTemplateName()
    {
        return "newsletter/templates/blocks/text/settings.hbs";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  42 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "newsletter/templates/blocks/text/settings.hbs", "C:\\xampp\\htdocs\\ubagod\\wp-content\\plugins\\mailpoet\\views\\newsletter\\templates\\blocks\\text\\settings.hbs");
    }
}
