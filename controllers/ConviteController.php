<?php

declare(strict_types=1);

class ConviteController extends Controller
{
    public function index(): void
    {
        $ref = strtoupper(trim((string) ($_GET['ref'] ?? '')));
        $error = '';
        $valid = false;

        $indicadorPrimeiroNome = '';
        $indicadorNomeCompleto = '';

        if ($ref === '') {
            $error = 'Link de convite inválido.';
        } else {
            $linkModel = new LinkIndicacao();
            $cliqueModel = new Clique();
            $referral = new ReferralService();

            // Check if link exists and is active
            $link = $linkModel->findByCodigo($ref);

            if ($link === null) {
                $error = 'Link de convite inválido.';
            } elseif (!$link['ativo']) {
                $error = 'Link de convite inativo.';
            } else {
                // Track click with spam/duplicate protection
                $ip = $_SERVER['REMOTE_ADDR'] ?? null;
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

                if ($cliqueModel->isSpam($ref, $ip)) {
                    $error = 'Muitas tentativas. Tente novamente mais tarde.';
                } elseif ($cliqueModel->isDuplicateClick($ref, $ip)) {
                    // Still valid, but don't count duplicate click
                    $valid = true;
                } else {
                    // Register click and increment counter
                    $cliqueModel->register($ref, $ip, $userAgent);
                    $linkModel->incrementCliques($ref);
                    $valid = true;
                }

                // Also handle referral service logic
                if ($valid) {
                    $result = $referral->handleLinkAccess($ref);
                    if (!$result['valid']) {
                        $error = $result['error'];
                        $valid = false;
                    } else {
                        $referrer = $result['user'] ?? null;
                        if (is_array($referrer)) {
                            $indicadorNomeCompleto = trim((string) ($referrer['nome'] ?? ''));
                            $indicadorPrimeiroNome = first_name_from_full($indicadorNomeCompleto);
                        }
                    }
                }
            }
        }

        $this->view('convite.index', [
            'title' => 'Você foi indicado!',
            'ref' => $ref,
            'error' => $error,
            'valid' => $valid,
            'indicadorPrimeiroNome' => $indicadorPrimeiroNome,
            'appDownloadUrl' => app_download_url(),
        ], 'convite');
    }

    public function participar(): void
    {
        if (!Csrf::validateRequest()) {
            Session::flash('errors', ['_form' => 'Token de segurança inválido.']);
            $this->redirect('/convite');
        }

        $ref = strtoupper(trim((string) ($_POST['ref'] ?? '')));
        $referral = new ReferralService();

        if ($ref === '') {
            Session::flash('errors', ['_form' => 'Código de convite inválido.']);
            $this->redirect('/convite');
        }

        $result = $referral->validateCode($ref, Auth::id());

        if (!$result['valid']) {
            Session::flash('errors', ['_form' => $result['error']]);
            $this->redirect('/convite?ref=' . urlencode($ref));
        }

        $referral->storeRefInSession($ref);
        $this->redirect('/cadastro');
    }
}
