@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center background d-flex align-items-center">
            <div class="col-md-10 mt-5 ">
                <div class="card border bg-white border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class='big bold'>Terms & Conditions</h4>
                        <hr>
                        <p>The use of {{ config('app.name', 'Laravel') }}'s services is subject to the terms and conditions listed below. Before
                            using our website, please carefully read through them. You accept to fully abide by the terms
                            and conditions by using our website.</p>

                        <p>If you disagree with any portion of the terms and conditions, please do not use our website. The
                            terms and conditions also list the many services that {{ config('app.name', 'Laravel') }} provides. The terms and
                            conditions are subject to change at {{ config('app.name', 'Laravel') }}'s discretion, and if you use the website after
                            those changes have been made, it will be assumed that you have accepted those changes.</p>

                        <p>Accessing the resources needed to use our site is your responsibility, and if you are unable to
                            we are not responsible for how you access the site on this ground.</p>

                        <p><b>Acceptance</b><br>
                            Once a user accesses and makes use of our website, the terms and conditions take effect, and a
                            binding contract between the user and {{ config('app.name', 'Laravel') }} and its affiliates is created.</p>

                        <p><b>General</b><br>
                            The following terminology will be used in our terms and conditions with the following
                            definitions. {{ config('app.name', 'Laravel') }} is meant by "We/Us/Our." "You/Your/User" refers to the person using
                            the website.
                            These terms and conditions are in place to govern how our website is used and apply to any
                            business dealings, products, and services made available on it.</p>

                        <p><b>Use of Material</b><br>
                            Our terms and conditions as well as the information and contents on our website are subject to
                            change. It is forbidden to use {{ config('app.name', 'Laravel') }}'s websites and systems without authorization.
                            {{ config('app.name', 'Laravel') }} decides who qualifies for particular services.</p>

                        <p><b>No Warranties</b><br>

                            By accepting our terms and conditions, you agree that your use of our website is entirely at
                            your own risk. We make no warranties, representations, or endorsements, either express or
                            implied. We expressly disclaim liability for typographical or grammatical errors in our
                            materials. We do not guarantee that access to our site will always be timely, error-free, or
                            secure, or that there will be no failures, data loss, or viruses.</p>

                        <p>However, serious steps are taken to ensure the accuracy of all our materials and information. You
                            will be responsible for determining the validity of our products and services, and you should
                            seek professional advice before making any commercial decisions based on the materials and
                            information on our site.</p>

                        <p><b>Links to Third Party Sites</b><br>
                            Our website may contain links to third-party websites. As a result, {{ config('app.name', 'Laravel') }} disclaims
                            liability for any content posted at any of the third-party websites linked to our website. By
                            posting a third-party link to our website, Live Auction does not endorse the products and
                            services offered, but rather provides these links for your convenience.</p>

                        <p><b>Limitation of Liability</b><br>
                            {{ config('app.name', 'Laravel') }} will not be liable to the user or any third party for any loss or damage incurred
                            as a result of your use of the site or any link posted on our site. Users access the website
                            entirely at their own risk. If a user disagrees with the information contained in the user is
                            advised not to use the site or the terms and conditions.</p>

                        <p><b>Indemnity</b><br>
                            The user agrees to defend, indemnify, and hold {{ config('app.name', 'Laravel') }} harmless from any claims, actions,
                            liabilities, costs, or demands, including without limitation legal and accounting fees,
                            resulting from your, or you acting on behalf of another person, including without limitation
                            governmental agencies, use, misuse, or inability to use the site or any of the materials
                            contained therein, or your breach of any of the terms and conditions o Any such claim will be
                            communicated to the user by {{ config('app.name', 'Laravel') }}. The user will be notified of any such claim or suit,
                            and the user will be responsible for its defense.</p>

                        <p><b>Events of Force Majeure</b><br>
                            {{ config('app.name', 'Laravel') }} will not be held liable for failure to perform as a result of unavoidable
                            circumstances such as natural disasters, political instabilities, infrastructure failure, or
                            hacking.</p>

                        <p><b>Non-Waiver, Severability, and Entire Agreement</b><br>
                            If a court of law with jurisdiction rules that any part of these terms and conditions is
                            invalid, the validity of the remaining parts will not be affected and will remain in effect.
                            Unless otherwise specified on a specific web page, these terms and conditions constitute the
                            entire agreement between the user and {{ config('app.name', 'Laravel') }}.</p>

                        <p><b>Account Suspension ad Termination of Access</b><br>
                            {{ config('app.name', 'Laravel') }} has the right to terminate or suspend a user’s access to our site at any time for
                            any reason. A user may experience suspension or termination if he or she violates the terms and
                            conditions provide, or violate the rights of {{ config('app.name', 'Laravel') }} or any of its third parties.</p>

                        <p><b>Governing Law and Jurisdiction</b><br>
                            The terms and conditions of our site and any matters that may result out of them are to be
                            construed per the law excluding any conflict of law provisions. With the user agreeing to the
                            terms and conditions, you thereby give consent to the exclusive jurisdiction of the court in all
                            disagreements resulting from the use of our site.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
