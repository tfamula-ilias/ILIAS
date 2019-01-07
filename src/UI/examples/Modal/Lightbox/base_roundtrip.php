<?php
function base_roundtrip()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$modal = $factory->modal()->roundtrip('My Modal 1', $factory->legacy('My Content'))
		->withActionButtons([
			$factory->button()->primary('Primary Action', ''),
			$factory->button()->standard('Secondary Action', ''),
		]);

	$out = '';
	$button1 = $factory->button()->standard('Open Modal 1', '#')
		->withOnClick($modal->getShowSignal());
	$out .= ' ' . $renderer->render($button1);

	$button2 = $button1->withLabel('Also opens modal 1');
	$out .= ' ' . $renderer->render($button2);

	$button3 = $button2->withLabel('Does not open modal 1')
		->withResetTriggeredSignals();
	$out .= ' ' . $renderer->render($button3);

	return $out . $renderer->render($modal);
}