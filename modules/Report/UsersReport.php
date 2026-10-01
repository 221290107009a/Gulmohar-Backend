<?php

namespace Modules\Report;

use Modules\User\Entities\User;
use Illuminate\Support\Carbon;

class UsersReport extends Report
{
    /**
     * Define the filters allowed for this report.
     *
     * @var array
     */
    protected $filters = ['from', 'to', 'customer_type', 'state', 'address', 'buddha_vihar'];

    /**
     * The date column to use for filtering.
     *
     * @var string
     */
    protected $date = 'users.created_at';

    /**
     * Get the view of the report index.
     *
     * @return string
     */
    protected function view()
    {
        return 'report::admin.user_reports.index';
    }

    /**
     * Override the render method for consistent 50-record pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function render($request)
    {
        $report = $this->report($request)
            ->paginate(50)
            ->appends($request->query());

        $showReport = $request->anyFilled(['from', 'to', 'customer_type', 'state', 'address', 'buddha_vihar']);

        return view($this->view())
            ->with(array_merge(compact('report', 'showReport'), $this->data()));
    }

    /**
     * Get the initial query for the detailed user list.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        $locale = app()->getLocale();
        
        return \DB::table('users')
            ->select(
                'users.id', 
                'users.first_name', 
                'users.last_name', 
                'users.fullname', 
                'users.email', 
                'users.state',
                'users.is_subscribe',
                'users.created_at',
                // Combined address from addresses table (limit to 1 to ensure unique per user)
                \DB::raw('(SELECT CONCAT_WS(", ", address_1, address_2, city) FROM addresses WHERE customer_id = users.id LIMIT 1) as address'),
                // Subqueries for Buddha Vihar info to avoid row duplication (Join causes issues with totals)
                \DB::raw('(SELECT GROUP_CONCAT(buddha_vihar_translations.name SEPARATOR ", ") FROM buddha_vihars JOIN buddha_vihar_translations ON buddha_vihar_translations.buddha_vihar_id = buddha_vihars.id WHERE buddha_vihars.user_id = users.id AND buddha_vihar_translations.locale = "'.$locale.'") as buddha_vihar_names'),
                \DB::raw('(SELECT GROUP_CONCAT(buddha_vihars.id SEPARATOR ",") FROM buddha_vihars WHERE user_id = users.id) as buddha_vihar_ids')
            )
            ->whereNotExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('user_roles')
                    ->whereColumn('user_roles.user_id', 'users.id')
                    ->where('role_id', 1);
            })
            ->orderByDesc('users.created_at');
    }

    /**
     * Filter by State.
     *
     * @param string $state
     * @return void
     */
    protected function state($state)
    {
        if ($state != '' && $state != 'all') {
            $this->query->where('users.state', trim($state));
        }
    }

    /**
     * Filter by Address.
     *
     * @param string $address
     * @return void
     */
    protected function address($address)
    {
        if ($address === 'added') {
            $this->query->whereExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('addresses')
                    ->whereColumn('addresses.customer_id', 'users.id');
            });
        } elseif ($address === 'not_added') {
            $this->query->whereNotExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('addresses')
                    ->whereColumn('addresses.customer_id', 'users.id');
            });
        }
    }

    /**
     * Filter by Buddha Vihar Name Status.
     *
     * @param string $vihar
     * @return void
     */
    protected function buddha_vihar($vihar)
    {
        if ($vihar === 'added') {
            $this->query->whereExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('buddha_vihars')
                    ->whereColumn('buddha_vihars.user_id', 'users.id');
            });
        } elseif ($vihar === 'not_added') {
            $this->query->whereNotExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('buddha_vihars')
                    ->whereColumn('buddha_vihars.user_id', 'users.id');
            });
        }
    }

    /**
     * Filter by Customer Type (Active/All).
     *
     * @param string $type
     * @return void
     */
    protected function customer_type($type)
    {
        if ($type === 'active') {
            $this->query->where('users.is_subscribe', 1);
        }
    }

    /**
     * Get summary data for the report.
     * 
     * @return array
     */
    protected function data()
    {
        $states = \DB::table('users')->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state');

        $totalQuery = \DB::table('users')
            ->whereNotExists(function($q) {
                $q->select(\DB::raw(1))
                    ->from('user_roles')
                    ->whereColumn('user_roles.user_id', 'users.id')
                    ->where('role_id', 1);
            })
            ->when(request()->has('state') && request('state') != '' && request('state') != 'all', function ($query) {
                return $query->where('users.state', trim(request('state')));
            })
            ->when(request()->has('customer_type') && request('customer_type') === 'active', function ($query) {
                return $query->where('users.is_subscribe', 1);
            })
            ->when(request()->has('address') && request('address') != '' && request('address') != 'all', function ($query) {
                if (request('address') === 'added') {
                    return $query->whereExists(function($q) {
                        $q->select(\DB::raw(1))
                            ->from('addresses')
                            ->whereColumn('addresses.customer_id', 'users.id');
                    });
                } elseif (request('address') === 'not_added') {
                    return $query->whereNotExists(function($q) {
                        $q->select(\DB::raw(1))
                            ->from('addresses')
                            ->whereColumn('addresses.customer_id', 'users.id');
                    });
                }
            })
            ->when(request()->has('buddha_vihar') && request('buddha_vihar') != '' && request('buddha_vihar') != 'all', function ($query) {
                if (request('buddha_vihar') === 'added') {
                    return $query->whereExists(function($q) {
                        $q->select(\DB::raw(1))
                            ->from('buddha_vihars')
                            ->whereColumn('buddha_vihars.user_id', 'users.id');
                    });
                } elseif (request('buddha_vihar') === 'not_added') {
                    return $query->whereNotExists(function($q) {
                        $q->select(\DB::raw(1))
                            ->from('buddha_vihars')
                            ->whereColumn('buddha_vihars.user_id', 'users.id');
                    });
                }
            })
            ->when(request()->has('from') && request('from') != '', function($query) {
                return $query->whereDate('users.created_at', '>=', Carbon::parse(request('from')));
            })
            ->when(request()->has('to') && request('to') != '', function($query) {
                return $query->whereDate('users.created_at', '<=', Carbon::parse(request('to')));
            });

        return [
            'grandTotal' => $totalQuery->count(),
            'states' => $states
        ];
    }
}
