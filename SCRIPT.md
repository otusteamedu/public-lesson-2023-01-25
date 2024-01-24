# Валидация в Symfony

## Подготовка

1. Запускаем docker-контейнеры командой `docker-compose up -d`
2. Логинимся в контейнер `php` командой `docker exec -it php sh`. Дальнейшие команды выполняются из контейнера.
3. Устанавливаем зависимости командой `composer install`
4. Устанавливаем пакет `symfony/validator`

## Добавляем простые правила

1. Добавляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\NotBlank]
        public string $notEmptyString;
    
        #[Assert\NotNull]
        public string $notNullString;
    
        #[Assert\Type('integer')]
        public int $int;
        
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->notEmptyString = $request->request->get('notEmptyString');
            $result->notNullString = $request->request->get('notNullString');
            $result->int = $request->request->get('int');
            
            return $result;
        }
    }
    ```
2. Добавляем класс `App\Controller\Controller`
    ```php
    <?php
    
    namespace App\Controller;
    
    use App\Controller\Input\InputDTO;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Attribute\AsController;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    
    #[AsController]
    #[Route('/api/v1/validator-test', methods: ['POST'])]
    class Controller
    {
        public function __construct(
            private readonly ValidatorInterface $validator
        ) {
        }
    
        public function __invoke(Request $request): Response
        {
            $dto = InputDTO::fillFromRequest($request);
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return new Response((string)$errors, Response::HTTP_BAD_REQUEST);
            }
    
            return new Response('Success');
        }
    }
    ```
3. Добавляем файл `config/routes/attributes.yaml`
```yaml
controllers:
  resource:
    path: ../../src/Controller/
    namespace: App\Controller
  type: attribute
```
4. Отправляем запрос `Validator simple test` из Postman-коллекции, получаем ответ с кодом 200
5. Отправляем запрос `Validator simple test` с пустым параметром `notEmptyString`, получаем ответ с кодом 400 и текстом
   ошибки
6. Отправляем запрос `Validator simple test` с пустым параметром `notNullString`, получаем ответ с кодом 200
7. Отправляем запрос `Validator simple test` без параметра `notNullString`, получаем ответ с кодом 500
8. Отправляем запрос `Validator simple test` с параметром `int`, равным строке, не являющейся числом, получаем ответ с
   кодом 500
9. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\NotBlank]
        public string $notEmptyString;
    
        #[Assert\NotNull]
        public ?string $notNullString;
    
        #[Assert\Type('integer')]
        public $int;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->notEmptyString = $request->request->get('notEmptyString');
            $result->notNullString = $request->request->get('notNullString');
            $result->int = $request->request->get('int');
    
            return $result;
        }
    }
    ```
7. Отправляем запрос `Validator simple test` без параметра `notNullString`, получаем ответ с кодом 400 и текстом ошибки
8. Отправляем запрос `Validator simple test` с параметром `int`, равным строке, не являющейся числом, получаем ответ с
   кодом 400 и текстом ошибки

## Добавляем правило типа Choice

1. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\Choice(choices: ['correct', 'valid'])]
        public string $choice;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->choice = $request->request->get('choice');
    
            return $result;
        }
    }
    ```
2. Отправляем запрос `Validator choice test`, видим ответ с кодом 200
3. Отправляем запрос `Validator choice test` с параметром `choice = bad`, видим ответ с кодом 400 и текстом ошибки

## Добавляем правило для проверки массива

1. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\Choice(choices: ['correct', 'valid'], multiple: true)]
        #[Assert\Type('array')]
        public array $choices;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->choices = $request->request->all('choices');
    
            return $result;
        }
    }
    ```
2. Отправляем запрос `Validator array test`, видим ответ с кодом 200
3. Отправляем запрос `Validator array test` со вторым параметром `choices[] = bad`, видим ответ с кодом 400 и текстом
   ошибки
4. Отправляем запрос `Validator array test` со единственным параметром `choices = valid`, видим ответ с кодом 400, но
   ошибку выдаёт не компонент валидации

## Добавляем валидацию для enum

1. Добавляем перечисление `App\Enums\ValueEnum`
    ```php
    <?php
    
    namespace App\Enums;
    
    enum ValueEnum: string
    {
        case Valid = 'valid';
        case Correct = 'correct';
    }
    ```
2. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use App\Enums\ValueEnum;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\Choice(callback: [ValueEnum::class, 'cases'], multiple: true)]
        #[Assert\Type('array')]
        public array $choices;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->choices = array_map(
                static fn(string $choice): ValueEnum => ValueEnum::from($choice),
                $request->request->all('choices')
            );
    
            return $result;
        }
    }
    ```
3. Отправляем запрос `Validator array test`, видим ответ с кодом 200
4. Отправляем запрос `Validator array test` со вторым параметром `choices[] = bad`, видим ответ с кодом 500
5. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use App\Enums\ValueEnum;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\Choice(callback: [ValueEnum::class, 'cases'], multiple: true)]
        #[Assert\Type('array')]
        public array $choices;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->choices = array_map(
                static fn(string $choice): ?ValueEnum => ValueEnum::tryFrom($choice),
                $request->request->all('choices')
            );
    
            return $result;
        }
    }
    ```

## Добавляем условную валидацию через выражение

1. Устанавливаем пакет `symfony/expression-language`
2. Добавляем класс `App\Enums\TypeEnum`
    ```php
    <?php
    
    namespace App\Enums;
    
    enum TypeEnum: string
    {
        case Absolute = 'absolute';
        case Relative = 'relative';

        public static function values(): array
        {
            return array_map(static fn(TypeEnum $value): string => $value->value, self::cases());
        }
    }
    ```
3. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use App\Enums\TypeEnum;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    #[Assert\Expression(
        "(this.type === 'absolute' and this.value > -1000 and this.value < 1000) or
         (this.type === 'relative' and this.value >= 0 and this.value <= 100)"
    )]
    class InputDTO
    {
        public int $value;
    
        #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
        public string $type;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->type = $request->request->get('type');
            $result->value = $request->request->get('value');
    
            return $result;
        }
    }
    ```
4. Отправляем запрос `Validator conditional test`, видим ответ с кодом 200
5. Отправляем запрос `Validator conditional test` с параметром `value = -2000`, видим ответ с кодом 400 и текстом ошибки
6. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = -20`, видим ответ с кодом
   400 и текстом ошибки
7. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 99`, видим ответ с кодом
   200
8. Отправляем запрос `Validator conditional test` с параметрами `type = other`, видим ответ с кодом 400 и текстом ошибки

## Добавляем условную валидацию через constraint When

1. Добавляем перечисление `App\Enums\NameEnum`
    ```php
    <?php
    
    namespace App\Enums;
    
    enum NameEnum: int
    {
        case Large = 20;
        case Medium = 15;
        case Small = 10;
        
        public static function values(): array
        {
            return array_map(static fn(NameEnum $value): string => $value->value, self::cases());
        }

        public static function names(): array
        {
            return array_map(static fn(NameEnum $value): string => $value->name, self::cases());
        }

        public static function getValueByName(string $name): ?int
        {
            return array_combine(self::names(), self::cases())[$name] ?? null;
        }
    }
    ```
2. Исправляем перечисление `App\Enums\TypeEnum`
    ```php
    <?php
    
    namespace App\Enums;
    
    enum TypeEnum: string
    {
        case Absolute = 'absolute';
        case Relative = 'relative';
        case Name = 'name';
    
        public static function values(): array
        {
            return array_map(static fn(TypeEnum $value): string => $value->value, self::cases());
        }
    }
    ```
3. Исправляем класс `App\Controller\Input\InputDTO`
   ```php
   <?php
   
   namespace App\Controller\Input;
   
   use App\Enums\TypeEnum;
   use Symfony\Component\HttpFoundation\Request;
   use Symfony\Component\Validator\Constraints as Assert;
   
   class InputDTO
   {
       #[Assert\When(
           expression: "this.type === 'absolute'",
           constraints: [
               new Assert\GreaterThan(-1000),
               new Assert\LessThan(1000),
           ]
       )]
       #[Assert\When(
           expression: "this.type === 'relative'",
           constraints: [
               new Assert\GreaterThanOrEqual(0),
               new Assert\LessThanOrEqual(100),
           ]
       )]
       #[Assert\When(
           expression: "this.type === 'name'",
           constraints: [
               new Assert\NotNull(),
               new Assert\Choice(callback: [NameEnum::class, 'names']),
           ]
       )]
       public mixed $value;
   
       #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
       public string $type;
   
       public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->type = $request->request->get('type');
            $result->value = $request->request->get('value');
   
           return $result;
       }
   }
   ```
4. Отправляем запрос `Validator conditional test`, видим ответ с кодом 200
5. Отправляем запрос `Validator conditional test` с параметром `value = -2000`, видим ответ с кодом 400 и текстом ошибки
6. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = -20`, видим ответ с кодом
   400 и текстом ошибки
7. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 99`, видим ответ с кодом
   200
8. Отправляем запрос `Validator conditional test` с параметрами `type = other`, видим ответ с кодом 400 и текстом ошибки
9. Отправляем запрос `Validator conditional test` с параметрами `type = name` и `value = other`, видим ответ с кодом 400
   и текстом ошибки
9. Отправляем запрос `Validator conditional test` с параметрами `type = name` и `value = Large`, видим ответ с кодом 200

## Объединяем существующие ограничения в комплексное ограничение

1. Добавляем класс `App\Constraint\RangeConstraint`
    ```php
    <?php
    
    namespace App\Constraint;
    
    use Attribute;
    use Symfony\Component\Validator\Constraints\Compound;
    use Symfony\Component\Validator\Constraints as Assert;
    
    #[Attribute]
    class RangeConstraint extends Compound
    {
        protected function getConstraints(array $options): array
        {
            return [
                new Assert\NotNull(),
                new Assert\GreaterThanOrEqual($options['payload']['min']),
                new Assert\LessThan($options['payload']['max']),
            ];
        }
    }
    ```
2. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use App\Constraint\RangeConstraint;
    use App\Enums\NameEnum;
    use App\Enums\TypeEnum;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\When(
            expression: "this.type === 'absolute'",
            constraints: [
                new Assert\GreaterThan(-1000),
                new Assert\LessThan(1000),
            ]
        )]
        #[Assert\When(
            expression: "this.type === 'relative'",
            constraints: [
                new RangeConstraint(['payload' => ['min' => 0, 'max' => 100]]),
            ]
        )]
        #[Assert\When(
            expression: "this.type === 'name'",
            constraints: [
                new Assert\NotNull(),
                new Assert\Choice(callback: [NameEnum::class, 'names']),
            ]
        )]
        public mixed $value;
    
        #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
        public string $type;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->type = $request->request->get('type');
            $result->value = $request->request->get('value');
    
    
            return $result;
        }
    }
    ```
3. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 5`, видим ответ с кодом 200
4. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = -5`, видим ответ с кодом
   400 и текстом ошибки
5. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 101`, видим ответ с кодом
   400 и текстом ошибки

## Добавляем кастомное ограничение

1. Добавляем класс `App\Constraint\CustomRangeConstraint`
    ```php
    <?php
    
    namespace App\Constraint;
    
    use Attribute;
    use Symfony\Component\Validator\Attribute\HasNamedArguments;
    use Symfony\Component\Validator\Constraint;
    
    #[Attribute]
    class CustomRangeConstraint extends Constraint
    {
        #[HasNamedArguments]
        public function __construct(
            public readonly int $min,
            public readonly int $max,
            array $groups = null,
            $payload = null
        ) {
            parent::__construct([], $groups, $payload);
        }
    }
    ```
2. Добавляем класс `App\Constraint\CustomConstraintValidator`
    ```php
    <?php
    
    namespace App\Constraint;
    
    use Symfony\Component\Validator\Constraint;
    use Symfony\Component\Validator\ConstraintValidator;
    use Symfony\Component\Validator\Exception\UnexpectedTypeException;
    use Symfony\Component\Validator\Exception\UnexpectedValueException;
    
    class CustomRangeConstraintValidator extends ConstraintValidator
    {
        public function validate(mixed $value, Constraint $constraint): void
        {
            if (!$constraint instanceof CustomRangeConstraint) {
                throw new UnexpectedTypeException($constraint, CustomRangeConstraint::class);
            }
    
            if (!is_numeric($value)) {
                throw new UnexpectedValueException($value, 'int');
            }
    
            if ($value >= $constraint->min && $value <= $constraint->max) {
                return;
            }
    
            $this->context->buildViolation('Value should be in range [{{ min }}, {{ max }}].')
                ->setParameter('{{ min }}', $constraint->min)
                ->setParameter('{{ max }}', $constraint->max)
                ->addViolation();
        }
    }
    ```
3. Исправляем класс `App\Controller\Input\InputDTO`
    ```php
    <?php
    
    namespace App\Controller\Input;
    
    use App\Constraint\CustomRangeConstraint;
    use App\Constraint\RangeConstraint;
    use App\Enums\NameEnum;
    use App\Enums\TypeEnum;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\Validator\Constraints as Assert;
    
    class InputDTO
    {
        #[Assert\When(
            expression: "this.type === 'absolute'",
            constraints: [
                new Assert\GreaterThan(-1000),
                new Assert\LessThan(1000),
            ]
        )]
        #[Assert\When(
            expression: "this.type === 'relative'",
            constraints: [
                new CustomRangeConstraint(min: 0, max: 100),
            ]
        )]
        #[Assert\When(
            expression: "this.type === 'name'",
            constraints: [
                new Assert\NotNull(),
                new Assert\Choice(callback: [NameEnum::class, 'names']),
            ]
        )]
        public mixed $value;
    
        #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
        public string $type;
    
        public static function fillFromRequest(Request $request): self {
            $result = new self();
            $result->type = $request->request->get('type');
            $result->value = $request->request->get('value');
    
    
            return $result;
        }
    }
    ```
3. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 5`, видим ответ с кодом 200
4. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = -5`, видим ответ с кодом
   400 и текстом ошибки
5. Отправляем запрос `Validator conditional test` с параметрами `type = relative` и `value = 101`, видим ответ с кодом
   400 и текстом ошибки
