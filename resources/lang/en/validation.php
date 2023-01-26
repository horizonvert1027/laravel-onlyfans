<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute deve ser aceito.',
    'active_url'           => ':attribute não é um URL válido.',
    'after'                => ':attribute deve ser uma data depois :date.',
    'after_or_equal'       => ':attribute deve ser uma data posterior ou igual a :date.',
    'alpha'                => ':attribute só pode conter letras.',
    'alpha_dash'           => ':attribute pode conter apenas letras, números e hífens.',
    "ascii_only"           => ":attribute pode conter apenas letras, números e hífens.",
    'alpha_num'            => ':attribute só pode conter letras e números.',
    'array'                => ':attribute deve ser uma matriz.',
    'before'               => ':attribute deve ser uma data antes :date.',
    'before_or_equal'      => ' :attribute deve ser uma data anterior ou igual a :date.',
    'between'              => [
        'numeric' => ':attribute deve estar entre :min e :max.',
        'file'    => ':attribute deve estar entre :min e :max kilobytes.',
        'string'  => ':attribute deve estar entre :min e :max caracteres.',
        'array'   => ':attribute deve estar entre :min e :max itens.',
    ],
    'boolean'              => ':attribute campo deve ser verdadeiro ou falso.',
    'confirmed'            => ':attribute a confirmação não corresponde.',
    'date'                 => ':attribute não é uma data válida.',
    'date_format'          => ':attribute não corresponde ao formato :format.',
    'different'            => ':attribute e :other deve ser diferente.',
    'digits'               => ':attribute devemos ser :digits digitos.',
    'digits_between'       => ':attribute deve estar entre :min e :max digitos.',
    'dimensions'           => 'Adicione :attribute com as dimensões superior à (:min_width x :min_height px).',
    'distinct'             => ':attribute campo tem um valor duplicado.',
    'email'                => ':attribute deve ser um endereço de email válido.',
    'exists'               => 'O :attribute selecionado é inválido.',
    'file'                 => 'O :attribute deve ser um arquivo.',
    'filled'               => 'O :attribute campo deve ter um valor.',
    'gt'                   => [
        'numeric' => ':attribute deve ser maior que :value.',
        'file'    => ':attribute deve ser maior que :value kilobytes.',
        'string'  => ':attribute deve ser maior que :value caracteres.',
        'array'   => ':attribute deve ser maior que :value itens.',
    ],
    'gte'                  => [
        'numeric' => ':attribute deve ser maior ou igual :value.',
        'file'    => ':attribute deve ser maior ou igual :value kilobytes.',
        'string'  => ':attribute deve ser maior ou igual :value caracteres.',
        'array'   => ':attribute deve ter :value itens ou mais.',
    ],
    'image'                => ':attribute deve ser uma imagem.',
    'in'                   => 'O :attribute selecionado é inválido.',
    'in_array'             => ':attribute campo não existe em :other.',
    'integer'              => ':attribute deve ser um número inteiro.',
    'ip'                   => ':attribute deve ser um endereço IP válido.',
    'ipv4'                 => ':attribute deve ser um endereço IPv4 válido.',
    'ipv6'                 => ':attribute deve ser um endereço IPv6 válido.',
    'json'                 => ':attribute deve ser uma string JSON válida.',
    'lt'                   => [
        'numeric' => ':attribute deve ser menor que :value.',
        'file'    => ':attribute deve ser menor que :value kilobytes.',
        'string'  => ':attribute deve ser menor que :value caracteres.',
        'array'   => ':attribute deve ser menor que :value itens.',
    ],
    'lte'                  => [
        'numeric' => ':attribute deve ser menor ou igual :value.',
        'file'    => ':attribute deve ser menor ou igual :value kilobytes.',
        'string'  => ':attribute deve ser menor ou igual :value caracteres.',
        'array'   => ':attribute não deve ter mais than :value itens.',
    ],
    'max'                  => [
        'numeric' => ':attribute pode não ser maior que :max.',
        'file'    => ':attribute pode não ser maior que :max kilobytes.',
        'string'  => ':attribute pode não ser maior que :max caracteres.',
        'array'   => ':attribute não pode ter mais do que :max itens.',
    ],
    'mimes'                => ':attribute deve ser um arquivo do tipo: :values.',
    'mimetypes'            => ':attribute deve ser um arquivo do tipo: :values.',
    'min'                  => [
        'numeric' => ':attribute deve ser pelo menos :min.',
        'file'    => ':attribute deve ser pelo menos :min kilobytes.',
        'string'  => ':attribute deve ser pelo menos :min caracteres.',
        'array'   => ':attribute deve ser pelo menos :min itens.',
    ],
    'not_in'               => 'O :attribute selecionado é inválido.',
    'not_regex'            => ':attribute formato é inválido.',
    'numeric'              => ':attribute deve ser um número.',
    'present'              => ':attribute campo deve estar presente.',
    'regex'                => ':attribute formato é inválido.',
    'required'             => ':attribute é obrigatório.',
    'required_if'          => ':attribute é obrigatório quando :other é :value.',
    'required_unless'      => ':attribute é obrigatório a menos que :other está em :values.',
    'required_with'        => ':attribute é obrigatório quando :values está presente.',
    'required_with_all'    => ':attribute é obrigatório quando :values está presente.',
    'required_without'     => ':attribute é obrigatório quando :values não está presente.',
    'required_without_all' => ':attribute é obrigatório quando nenhum dos :values não estão presentes.',
    'same'                 => ':attribute e :other deve combinar.',
    'size'                 => [
        'numeric' => ':attribute deve ser :size.',
        'file'    => ':attribute deve ser :size kilobytes.',
        'string'  => ':attribute deve ser :size caracteres.',
        'array'   => ':attribute deve conter :size itens.',
    ],
    'string'               => ':attribute deve ser uma string.',
    'timezone'             => ':attribute deve ser uma zona válida.',
    'unique'               => ':attribute já foi usado.',
    'uploaded'             => ':attribute falha ao carregar.',
    'url'                  => ':attribute formato é inválido.',
    "account_not_confirmed" => "Sua conta não foi confirmada, verifique seu email",
  	"user_suspended"        => "Sua conta foi suspensa, entre em contato conosco se houver um erro",
  	"letters"              => "O :attribute deve conter pelo menos uma letra ou número",
    'video_url'          => 'URL inválido suporta apenas Youtube e Vimeo.',
    'update_max_length' => 'O post não pode ser maior que :max caracteres.',
    'update_min_length' => 'O post deve ter pelo menos :min caracteres.',
    'video_url_required'   => 'O campo URL do vídeo é obrigatório quando o conteúdo em destaque é um vídeo.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
  		'agree_gdpr' => 'Concordo com o tratamento de dados pessoais',
      'agree_terms' => 'Eu concordo com os Termos e Condições',
      'agree_terms_privacy' => 'Eu concordo com os Termos e Condições e Política de Privacidade',
  		'full_name' => 'Nome completo',
      'name' => 'Nome',
  		'username'  => 'Nome de usuário',
      'username_email' => 'Nome de usuário ou Email',
  		'email'     => 'Email',
  		'password'  => 'Senha',
  		'password_confirmation' => 'Confirmação da senha',
  		'website'   => 'Site',
  		'location' => 'Localização',
  		'countries_id' => 'País',
  		'twitter'   => 'Twitter',
  		'facebook'   => 'Facebook',
  		'google'   => 'Google',
  		'instagram'   => 'Instagram',
  		'comment' => 'Comentar',
  		'title' => 'Título',
  		'description' => 'Descrição',
      'old_password' => 'Senha antiga',
      'new_password' => 'Nova senha',
      'email_paypal' => 'Email PayPal',
      'email_paypal_confirmation' => 'Email PayPal confirmation',
      'bank_details' => 'Dados bancários',
      'video_url' => 'Video URL',
      'categories_id' => 'Província',
      'story' => 'Estado',
      'image' => 'Imagem',
      'avatar' => 'Foto de perfil',
      'message' => 'Messagem',
      'profession' => 'Profissão',
      'thumbnail' => 'Thumbnail',
      'address' => 'Endereço',
      'city' => 'Cidade',
      'zip' => 'Postal/ZIP',
      'payment_gateway' => 'Método de pagamento',
      'payment_gateway_tip' => 'Método de pagamento',
      'MAIL_FROM_ADDRESS' => 'Email no-reply',
      'FILESYSTEM_DRIVER' => 'Disk',
      'price' => 'Preço',
      'amount' => 'Montante',
      'telephone' => 'Número de telemóvel',
      'birthdate' => 'Data de nascimento',
      'navbar_background_color' => 'Navbar background color',
    	'navbar_text_color' => 'Navbar text color',
    	'footer_background_color' => 'Footer background color',
    	'footer_text_color' => 'Footer text color',

      'AWS_ACCESS_KEY_ID' => 'Amazon Key', // Not necessary edit
      'AWS_SECRET_ACCESS_KEY' => 'Amazon Secret', // Not necessary edit
      'AWS_DEFAULT_REGION' => 'Amazon Region', // Not necessary edit
      'AWS_BUCKET' => 'Amazon Bucket', // Not necessary edit

      'DOS_ACCESS_KEY_ID' => 'DigitalOcean Key', // Not necessary edit
      'DOS_SECRET_ACCESS_KEY' => 'DigitalOcean Secret', // Not necessary edit
      'DOS_DEFAULT_REGION' => 'DigitalOcean Region', // Not necessary edit
      'DOS_BUCKET' => 'DigitalOcean Bucket', // Not necessary edit

      'WAS_ACCESS_KEY_ID' => 'Wasabi Key', // Not necessary edit
      'WAS_SECRET_ACCESS_KEY' => 'Wasabi Secret', // Not necessary edit
      'WAS_DEFAULT_REGION' => 'Wasabi Region', // Not necessary edit
      'WAS_BUCKET' => 'Wasabi Bucket', // Not necessary edit

      //===== v2.0
      'BACKBLAZE_ACCOUNT_ID' => 'Backblaze Account ID', // Not necessary edit
      'BACKBLAZE_APP_KEY' => 'Backblaze Master Application Key', // Not necessary edit
      'BACKBLAZE_BUCKET' => 'Backblaze Bucket Name', // Not necessary edit
      'BACKBLAZE_BUCKET_REGION' => 'Backblaze Bucket Region', // Not necessary edit
      'BACKBLAZE_BUCKET_ID' => 'Backblaze Bucket Endpoint', // Not necessary edit

      'VULTR_ACCESS_KEY' => 'Vultr Key', // Not necessary edit
      'VULTR_SECRET_KEY' => 'Vultr Secret', // Not necessary edit
      'VULTR_REGION' => 'Vultr Region', // Not necessary edit
      'VULTR_BUCKET' => 'Vultr Bucket', // Not necessary edit
  	],

];
