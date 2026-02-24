<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentSubcategory;
use App\Models\EquipmentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipmentHierarchySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            $structure = [

                'Artificial lift equipment' => [
                    'ESP systems' => [
                        'Submersible pump assembly',
                        'Electric motor',
                        'Protector',
                        'Intake module',
                    ],
                    'Gas lift systems' => [
                        'Gas lift mandrel',
                        'Gas lift injection valve',
                    ],
                    'Rod Lift systems' => [
                        'Beam pump (pump jack)',
                        'Sucker rod',
                        'Rod pump',
                    ],
                    'PCP systems' => [
                        'Progressive cavity pump',
                        'Drive head',
                        'Controller'
                    ],
                    'Plunger Lift' => [
                        'Plunger',
                        'Lubricator',
                        'Bumper spring'
                    ],
                ],

                'Completion equipment' => [
                    'Production packers' => [
                        'Permanent packer',
                        'Retrievable packer',
                    ],
                    'Bridge plugs' => [
                        'Composite plug',
                        'Cast iron plug',
                    ],
                    'Sand Control Screens' => [
                        'Wire-wrapped screen',
                        'Expandable screen',
                        'Premium mesh screen'
                    ],
                    'Multistage Frac Systems' => [
                        'Sliding sleeve system',
                        'Plug-and-perf system',
                        'Ball-drop system'
                    ],
                    'Subsurface Safety Valves' => [
                        'Tubing-retrievable SSSV',
                        'Wireline-retrievable SSSV',
                    ],
                    'Smart Completions' => [
                        'Interval control valve',
                        'Downhole flow control module',
                    ],
                ],

                'Compression Equipment' => [
                    'Concrete Mixers' => [
                        'Truck-mounted concrete mixer',
                        'Portable concrete mixer',
                    ],
                    'Concrete Pumps' => [
                        'Boom pump truck',
                        'Trailer-mounted line pump',
                    ],
                ],

                'Concrete Equipment' => [
                    'Concrete Mixers' => [
                        'Truck-mounted concrete mixer',
                        'Portable concrete mixer',
                    ],
                    'Concrete Pumps' => [
                        'Boom pump truck',
                        'Trailer-mounted line pump',
                    ],
                ],

                'Drilling equipment' => [
                    'Land Drilling Rigs' => [
                        'Box-on-box rig',
                        'Desert rig',
                        'Fast-move skid rig',
                        'Trailer-mounted rig',
                    ],
                    'Jack-up Rigs' => [
                        'Cantilever jackup',
                        'Slot jackup',
                        'Harsh-environment jackup',
                    ],
                    'Semi-submersible Rigs' => [
                        'Moored semi-submersible',
                        'Dynamically positioned semi-submersible',
                    ],
                    'Drillships' => [
                        'Dynamically positioned drillship',
                        'Ultra-deepwater drillship',
                    ],
                    'Workover Rigs' => [
                        'Truck-mounted workover rig',
                        'Mast workover rig',
                        'Snubbing unit'
                    ],
                    'Rig Masts' => [
                        'Mast',
                        'Derrick',
                        'Monkey board'
                    ],
                    'Rig Substructures' => [
                        'Substructure',
                    ],
                    'Hoisting Systems' => [
                        'Drawworks',
                        'Crown block',
                        'Traveling block',
                        'Hook block'
                    ],
                    'Rotary Systems' => [
                        'Rotary table',
                        'Top drive',
                        'Kelly drive',
                        'Swivel'
                    ],
                    'Mud Circulation Systems' => [
                        'Standpipe',
                        'Mud manifold',
                        'Mud hose',
                        'Swivel joint',
                    ],
                    'Rig Power Systems' => [
                        'SCR house',
                        'VFD house',
                        'Rig generator',
                        'Engine skid'
                    ],
                    'Cementing Heads' => [
                        'Cementing head',
                        'Plug container',
                        'Cement manifold'
                    ],
                    'Cementing Pumps' => [
                        'Skid cement unit',
                        'Truck-mounted cementer',
                        'Batch mixer',
                    ],
                    'Casing Running Tools' => [
                        'Casing running tool (CRT)',
                        'Power slips',
                        'Casing tongs',
                        'Pick-up machine',
                    ],
                    'Well Control Equipment' => [
                        'BOP stack',
                        'Annular preventer',
                        'Ram preventer',
                        'Choke manifold',
                    ],
                    'Shale Shakers' => [
                        'Linear motion shaker',
                        'Elliptical shaker',
                        'Shaker screen',
                    ],
                    'Desanders' => [
                        'Cyclone desander',
                    ],
                    'Desilters' => [
                        'Mud cleaner',
                    ],
                    'Degassers' => [
                        'Vacuum degasser',
                        'Centrifugal degasser',
                    ],
                    'Centrifuges' => [
                        'High-speed centrifuge',
                        'Barite recovery centrifuge',
                    ],
                    'Mud Mixing Systems' => [
                        'Mixing hopper',
                        'Jet mixer',
                        'Mud gun',
                    ],
                    'Mud Tanks' => [
                        'Suction pit',
                        'Reserve pit',
                        'Compartment tank',
                    ],
                    'Bulk Handling' => [
                        'Bulk silo',
                        'Pneumatic blower',
                        'Manifold skid',
                    ],
                    'Drill Bits' => [
                        'PDC bit',
                        'Tricone bit',
                        'Coring bit',
                        'Bi-center bit'
                    ],
                    'Mud Motors' => [
                        'Positive displacement motor',
                        'High-torque mud motor'
                    ],
                    'Rotary Steerable Tools' => [
                        'Push-the-bit RSS (rotary steerable system)',
                        'Point-the-bit RSS',
                    ],
                    'MWD Tools' => [
                        'MWD (measurement-while-drilling) pulser',
                        'Gamma logging tool',
                        'Resistivity tool',
                        'Sonic tool',
                    ],
                    'LWD Tools' => [
                        'LWD (logging-while-drilling) sensor suite',
                    ],
                    'Stabilizers' => [
                        'Near-bit stabilizer'
                    ],
                    'Reamers' => [
                        'Underreamer',
                        'Hole opener',
                    ],
                    'Fishing Tools' => [
                        'Overshot',
                        'Spear',
                        'Junk basket',
                        'Magnet'
                    ],
                    'Whipstocks' => [
                        'Whipstock',
                    ],
                    'Mills' => [
                        'Casing mill',
                        'Window mill',
                    ],
                ],

                'Earthmoving equipment' => [
                    'Excavators' => [
                        'Crawler excavator',
                        'Wheeled excavator',
                        'Mini excavator',
                    ],
                    'Bulldozers' => [
                        'Crawler bulldozer',
                        'Wheeled bulldozer',
                    ],
                    'Loaders' => [
                        'Front-end loader',
                        'Skid-steer loader',
                    ],
                    'Graders' => [
                        'Motor grader',
                    ],
                ],

                'Electrical equipment' => [
                    'Gas Turbines' => [
                        'Aeroderivative gas turbine',
                        'Heavy-duty gas turbine',
                    ],
                    'Generators' => [
                        'Diesel generator',
                        'Gas generator',
                    ],
                    'Transformers' => [
                        'Power transformer',
                        'Distribution transformer'
                    ],
                    'Switchgear' => [
                        'Medium-voltage switchgear',
                        'Low-voltage switchgear',
                    ],
                    'Switches' => [
                        'Isolator switch',
                        'Circuit breaker',
                    ],
                    'Motor Control Centers' => [
                        'MCC panel',
                    ],
                    'Drives' => [
                        'Variable speed drive (VSD)',
                        'Soft starter',
                    ],
                    'Distribution Boards' => [
                        'Main distribution board'
                    ],
                    'Electric Motors' => [
                        'Induction motor',
                        'Synchronous motor',
                    ],
                    'UPS Systems' => [
                        'Online UPS',
                        'Battery bank',
                    ],
                    'Electrical Substations' => [
                        'Prefabricated substation',
                    ],
                    'Cable Trays' => [
                        'Ladder-type cable tray',
                        'Perforated cable tray',
                    ],
                ],

                'Flow Control equipment' => [
                    'Choke Manifolds' => [
                        'Adjustable choke manifold',
                        'Positive choke manifold',
                    ],
                    'Test Manifolds' => [
                        'Fracturing test manifold',
                        'Production test manifold',
                    ],
                    'Metering Skids' => [
                        'Single-phase metering skid',
                        'Multiphase metering skid',
                    ],
                ],

                'Instrumentation equipment' => [
                    'Gauges' => [
                        'Pressure gauge',
                        'Temperature gauge',
                        'Level gauge'
                    ],
                    'Transmitters' => [
                        'Pressure transmitter',
                        'Temperature transmitter',
                        'Flow transmitter',
                        'Level transmitter'
                    ],
                    'Control Valves' => [
                        'Globe control valve',
                        'Ball control valve',
                        'Rotary control valve'
                    ],
                    'PLC Systems' => [
                        'Programmable logic controller (PLC)',
                    ],
                    'DCS Systems' => [
                        'Distributed control system (DCS) controller',
                    ],
                    'SIS Systems' => [
                        'Safety instrumented system (SIS) controller',
                    ],
                    'ESD Systems' => [
                        'Emergency shutdown (ESD) system controller',
                    ],
                    'Gas Detectors' => [
                        'Fixed gas detector',
                        'Portable gas detector',
                    ],
                    'Fire Detectors' => [
                        'Flame detector',
                        'Smoke detector',
                    ],
                    'Flow Regulators' => [
                        'Flow control valve',
                    ],
                    'Flow Indicators' => [
                        'Flow indicator',
                    ],
                    'Sensors' => [
                        'Temperature sensor',
                        'Level probe',
                    ],
                ],

            ];

            foreach ($structure as $categoryName => $subcategories) {

                $category = EquipmentCategory::firstOrCreate(
                    ['name' => $categoryName],
                    ['is_global' => true]
                );

                foreach ($subcategories as $subCategoryName => $types) {

                    $subCategory = EquipmentSubcategory::firstOrCreate(
                        [
                            'name' => $subCategoryName,
                            'equipment_category_id' => $category->id,
                        ]
                    );

                    foreach ($types as $typeName) {

                        EquipmentType::firstOrCreate(
                            [
                                'name' => $typeName,
                                'equipment_subcategory_id' => $subCategory->id,
                            ]
                        );
                    }
                }
            }
        });
    }
}
