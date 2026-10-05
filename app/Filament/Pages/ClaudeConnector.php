<?php

namespace App\Filament\Pages;

use App\Mcp\Support\ConnectorTokens;
use App\Mcp\Support\OAuthKeys;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Laravel\Passport\Token;
use UnitEnum;

/**
 * Site → Claude connector (docs/13-mcp-connector.md): how to connect Claude to the MCP server, personal
 * access tokens for clients that take a bearer token, and every app that is connected, with revoke.
 */
class ClaudeConnector extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCommandLine;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Claude connector';

    protected static ?string $title = 'Claude connector';

    protected static ?string $slug = 'claude';

    /**
     * A token that was just created. Shown once, never stored.
     */
    public ?string $plainToken = null;

    public function getSubheading(): string
    {
        return 'Let Claude read and edit your portfolio: add a project from a GitHub repository, fill in a case study, upload screenshots, keep skills and companies tidy.';
    }

    public static function serverUrl(): string
    {
        return url('/mcp');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createToken')
                ->label('Create token')
                ->icon(Heroicon::OutlinedKey)
                ->modalHeading('Create a personal access token')
                ->modalDescription('For Claude Code or any MCP client that takes a bearer token. claude.ai and Claude Desktop do not need one: they sign in with OAuth.')
                ->modalSubmitActionLabel('Create')
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->default('Claude Code')
                        ->required()
                        ->maxLength(100)
                        ->helperText('Where you will use it, so you can recognise it later.'),
                ])
                ->action(function (array $data): void {
                    $this->plainToken = ConnectorTokens::create($this->user(), (string) $data['name'])->accessToken;

                    Notification::make()->success()->title('Token created')->body('Copy it now: it is shown only once.')->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            ...$this->problems(),
            Section::make('Your new token')
                ->icon(Heroicon::OutlinedKey)
                ->description('Copy it now: it will not be shown again. Anyone with this token can edit your portfolio.')
                ->visible(fn (): bool => $this->plainToken !== null)
                ->schema([
                    TextEntry::make('plainToken')
                        ->hiddenLabel()
                        ->state(fn (): ?string => $this->plainToken)
                        ->fontFamily(FontFamily::Mono)
                        ->copyable()
                        ->copyMessage('Token copied')
                        ->extraAttributes(['class' => 'break-all']),
                    TextEntry::make('claudeCodeWithToken')
                        ->label('Claude Code')
                        ->state(fn (): string => 'claude mcp add --transport http portfolio '.self::serverUrl().' --header "Authorization: Bearer '.$this->plainToken.'"')
                        ->fontFamily(FontFamily::Mono)
                        ->copyable()
                        ->copyMessage('Command copied')
                        ->extraAttributes(['class' => 'break-all']),
                    Text::make('Next time, connect with OAuth instead and you will not need to handle a token at all.'),
                ]),
            Section::make('Connect Claude')
                ->icon(Heroicon::OutlinedLink)
                ->schema([
                    TextEntry::make('serverUrl')
                        ->label('Server URL')
                        ->state(self::serverUrl())
                        ->fontFamily(FontFamily::Mono)
                        ->copyable()
                        ->copyMessage('URL copied'),
                    Grid::make(['default' => 1, 'lg' => 2])->schema([
                        Section::make('claude.ai and Claude Desktop')
                            ->compact()
                            ->secondary()
                            ->schema([
                                Html::make(self::steps([
                                    'Open <strong>Settings → Connectors</strong> and choose <strong>Add custom connector</strong>.',
                                    'Name it “Portfolio” and paste the server URL above.',
                                    'Click <strong>Connect</strong>, sign in to this panel and approve.',
                                    'In a chat, turn the connector on and ask: “Add github.com/me/repo to my portfolio”.',
                                ])),
                            ]),
                        Section::make('Claude Code')
                            ->compact()
                            ->secondary()
                            ->schema([
                                TextEntry::make('claudeCode')
                                    ->hiddenLabel()
                                    ->state('claude mcp add --transport http portfolio '.self::serverUrl())
                                    ->fontFamily(FontFamily::Mono)
                                    ->copyable()
                                    ->copyMessage('Command copied'),
                                Text::make('Then run /mcp in Claude Code and choose Authenticate. Prefer a token? Use “Create token” above.'),
                            ]),
                    ]),
                ]),
            Section::make('Connected apps and tokens')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->description('Everything that can currently use the connector. Revoke anything you do not recognise.')
                ->schema([EmbeddedTable::make()]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ConnectorTokens::active($this->user()))
            ->paginated(false)
            ->emptyStateHeading('Nothing is connected yet')
            ->emptyStateDescription('Connect Claude with the steps above, or create a token.')
            ->emptyStateIcon(Heroicon::OutlinedLink)
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->state(fn (Token $record): string => ConnectorTokens::isPersonal($record) ? (string) $record->name : (string) $record->client?->name)
                    ->description(fn (Token $record): ?string => ConnectorTokens::isPersonal($record) ? null : $this->redirectHost($record)),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->state(fn (Token $record): string => ConnectorTokens::isPersonal($record) ? 'Personal token' : 'OAuth app')
                    ->color(fn (Token $record): string => ConnectorTokens::isPersonal($record) ? 'warning' : 'info'),
                TextColumn::make('created_at')->label('Connected')->since()->dateTimeTooltip(),
                TextColumn::make('expires_at')->label('Expires')->since()->dateTimeTooltip(),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revoke access?')
                    ->modalDescription('The app or token stops working immediately. Claude will ask you to connect again.')
                    ->action(function (Token $record): void {
                        ConnectorTokens::revoke($record);

                        Notification::make()->success()->title('Access revoked')->send();
                    }),
            ]);
    }

    /**
     * Setup problems that would stop Claude from connecting.
     *
     * @return list<Callout>
     */
    private function problems(): array
    {
        $problems = [];

        if (! config('portfolio.mcp.enabled')) {
            $problems[] = Callout::make('The connector is switched off')
                ->description('Set MCP_ENABLED=true in .env to turn it on.')
                ->danger();
        }

        foreach (OAuthKeys::problems() as $title => $fix) {
            $problems[] = Callout::make($title)->description($fix)->danger();
        }

        if (app()->isProduction() && ! str_starts_with(self::serverUrl(), 'https://')) {
            $problems[] = Callout::make('The site is not served over HTTPS')
                ->description('claude.ai only connects to https:// URLs. Check APP_URL and your TLS setup.')
                ->warning();
        }

        return $problems;
    }

    /**
     * @param  list<string>  $steps  trusted HTML
     */
    private static function steps(array $steps): HtmlString
    {
        return new HtmlString('<ol class="list-decimal space-y-1 ps-5 text-sm text-gray-700 dark:text-gray-300">'
            .implode('', array_map(fn (string $step): string => "<li>{$step}</li>", $steps))
            .'</ol>');
    }

    private function redirectHost(Token $token): ?string
    {
        $uris = $token->client->getAttribute('redirect_uris');
        $host = is_array($uris) && isset($uris[0]) ? parse_url((string) $uris[0], PHP_URL_HOST) : null;

        return is_string($host) ? "Returns to {$host}" : null;
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);

        return $user;
    }
}
