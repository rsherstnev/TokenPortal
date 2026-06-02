<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Формирование акта приёма-передачи из DOCX-шаблона (закладки Word).
 * Сгенерированный файл не сохраняется на диск — только в памяти.
 */
class Transfer_act_docx {

	/** @var string */
	private $template_path;

	public function __construct()
	{
		$this->template_path = APPPATH . 'templates/transfer_acceptance_act.docx';
	}

	/**
	 * @param array $data keys: city, date, from_who, token, to_who
	 * @return string binary DOCX
	 */
	public function render(array $data)
	{
		if ( ! is_readable($this->template_path))
		{
			throw new RuntimeException('Шаблон акта не найден');
		}

		if ( ! class_exists('ZipArchive'))
		{
			throw new RuntimeException('Расширение ZipArchive недоступно');
		}

		$zip = new ZipArchive();
		if ($zip->open($this->template_path) !== TRUE)
		{
			throw new RuntimeException('Не удалось открыть шаблон акта');
		}

		$document_xml = $zip->getFromName('word/document.xml');
		if ($document_xml === FALSE)
		{
			$zip->close();
			throw new RuntimeException('Некорректный шаблон акта');
		}

		$replacements = array(
			'City'    => (string) ($data['city'] ?? ''),
			'Date'    => (string) ($data['date'] ?? ''),
			'FromWho' => (string) ($data['from_who'] ?? ''),
			'Token'   => (string) ($data['token'] ?? ''),
			'ToWho'   => (string) ($data['to_who'] ?? ''),
		);

		foreach ($replacements as $bookmark => $text)
		{
			$document_xml = $this->fill_bookmark($document_xml, $bookmark, $text);
		}

		$out = new ZipArchive();
		$tmp = tempnam(sys_get_temp_dir(), 'act_');
		if ($tmp === FALSE || $out->open($tmp, ZipArchive::OVERWRITE) !== TRUE)
		{
			$zip->close();
			if ($tmp !== FALSE)
			{
				@unlink($tmp);
			}
			throw new RuntimeException('Не удалось подготовить документ');
		}

		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$name = $zip->getNameIndex($i);
			if ($name === FALSE)
			{
				continue;
			}
			$content = ($name === 'word/document.xml') ? $document_xml : $zip->getFromIndex($i);
			$out->addFromString($name, $content);
		}

		$zip->close();
		$out->close();

		$binary = file_get_contents($tmp);
		@unlink($tmp);

		if ($binary === FALSE)
		{
			throw new RuntimeException('Не удалось сформировать документ');
		}

		return $binary;
	}

	private function fill_bookmark($xml, $bookmark_name, $text)
	{
		$pattern = '#<w:bookmarkStart\b([^>]*)\bw:name="' . preg_quote($bookmark_name, '#') . '"([^>]*)/>#';
		if ( ! preg_match($pattern, $xml, $m, PREG_OFFSET_CAPTURE))
		{
			return $xml;
		}

		$attrs = $m[1][0] . $m[2][0];
		if ( ! preg_match('/\bw:id="(\d+)"/', $attrs, $id_m))
		{
			return $xml;
		}

		$bookmark_id = $id_m[1];
		$start_tag = $m[0][0];
		$start_pos = $m[0][1];

		$end_pattern = '#<w:bookmarkEnd\b[^>]*\bw:id="' . preg_quote($bookmark_id, '#') . '"[^>]*/>#';
		$after_start = substr($xml, $start_pos + strlen($start_tag));
		if ( ! preg_match($end_pattern, $after_start, $end_m, PREG_OFFSET_CAPTURE))
		{
			return $xml;
		}

		$run = '<w:r><w:t xml:space="preserve">' . $this->xml_escape($text) . '</w:t></w:r>';
		$insert_pos = $start_pos + strlen($start_tag);

		return substr($xml, 0, $insert_pos) . $run . substr($xml, $insert_pos);
	}

	private function xml_escape($text)
	{
		return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}
}
