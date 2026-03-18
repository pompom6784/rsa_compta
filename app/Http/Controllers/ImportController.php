<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CheckDeliveryImportService;
use App\Services\PaypalImportService;
use App\Services\SGImportService;
use App\Services\SogecomImportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function __construct(
        private PaypalImportService $paypalImportService,
        private SogecomImportService $sogecomImportService,
        private SGImportService $sgImportService,
        private CheckDeliveryImportService $checkDeliveryImportService,
    ) {
    }

    public function home(): Response
    {
        return response(view('imports'));
    }

    public function paypal(Request $request): Response
    {
        $file  = $request->file('importPaypal');
        $error = $this->getUploadError($file);

        if ($error === '' && $file !== null) {
            $realPath = $file->getRealPath();
            if ($realPath !== false) {
                $handle = fopen($realPath, 'r');
                if ($handle !== false) {
                    $this->paypalImportService->import($handle);
                    fclose($handle);
                }
            } else {
                $error = "Erreur système : chemin de fichier temporaire invalide";
            }
        }

        return response(view('imports', ['error' => $error]));
    }

    public function sogecom(Request $request): Response
    {
        $file  = $request->file('importSogecom');
        $error = $this->getUploadError($file);

        if ($error === '' && $file !== null) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle !== false) {
                $this->sogecomImportService->import($handle);
                fclose($handle);
            }
        }

        return response(view('imports', ['error' => $error]));
    }

    public function sg(Request $request): Response
    {
        $file  = $request->file('importSG');
        $error = $this->getUploadError($file);

        if ($error === '' && $file !== null) {
            $realPath = $file->getRealPath();
            if ($realPath !== false) {
                $handle = fopen($realPath, 'r');
                if ($handle !== false) {
                    $this->sgImportService->import($handle);
                    fclose($handle);
                }
            } else {
                $error = "Erreur système : chemin de fichier temporaire invalide";
            }
        }

        return response(view('imports', ['error' => $error]));
    }

    public function checkDelivery(Request $request): Response
    {
        $request->validate([
            'importCheckDelivery' => 'required|file|mimes:xlsx,xlsm|max:10240',
        ]);

        $file  = $request->file('importCheckDelivery');
        $error = $this->getUploadError($file);

        if ($error !== '') {
            return response(view('imports', ['error' => $error]));
        }

        if ($file === null) {
            return response(view('imports', ['error' => "Aucun fichier n'a été envoyé"]));
        }

        $realPath = $file->getRealPath();
        if ($realPath === false) {
            return response(view('imports', ['error' => "Erreur système : chemin de fichier temporaire invalide"]));
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($realPath);
        $this->checkDeliveryImportService->import($spreadsheet);

        return response(view('imports'));
    }

    private function getUploadError(?UploadedFile $file): string
    {
        if ($file === null) {
            return "Aucun fichier n'a été envoyé";
        }

        return match ($file->getError()) {
            UPLOAD_ERR_NO_FILE                         => "Aucun fichier n'a été envoyé",
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE  => 'Le fichier envoyé dépasse la taille maximale autorisée',
            UPLOAD_ERR_PARTIAL                         => "Le fichier n'a été que partiellement envoyé",
            UPLOAD_ERR_NO_TMP_DIR                      => 'Erreur système : aucun répertoire temporaire',
            UPLOAD_ERR_CANT_WRITE                      => "Erreur système : impossible d'écrire sur le disque",
            UPLOAD_ERR_EXTENSION                       => "Erreur système : une extension PHP a arrêté l'envoi de fichier",
            UPLOAD_ERR_OK                              => '',
            default                                    => "Erreur lors de l'import du fichier",
        };
    }
}
